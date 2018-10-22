<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

use App\UserBallot;
use App\Models\Candidate\ConsolidatedCandidate;
use App\Models\Election\ConsolidatedElection;
use App\News;

class BallotManager {

    /**
     * @param UserBallot $ballot
     * @return ConsolidatedElection[]
     */
    public function get_elections_from_ballot(UserBallot $ballot) {
        $elections = $this->get_elections_by_state($ballot->state_abbreviation);
        
        $ballot_candidates = collect($this->_get_candidates_from_ballot($elections, $ballot));

        $filtered_elections = $elections->whereIn('id', $ballot_candidates->pluck('election_id')->unique());

        foreach($filtered_elections as $filtered_election) {
            unset($filtered_election->candidates);
        }

        return $filtered_elections;
    }

    /**
     * @return ConsolidatedCandidate[]
     */
    public function get_candidates_from_ballot(UserBallot $ballot) {
        $elections = $this->get_elections_by_state($ballot->state_abbreviation);

        $selected_candidate_ids = $ballot->candidates;
        
        $candidates_collection = collect($this->_get_candidates_from_ballot($elections, $ballot));
        
        $candidates_collection = $candidates_collection->map(function($candidate) use ($selected_candidate_ids) {
            $candidate["selected"] = $selected_candidate_ids->contains($candidate["id"]);
            return $candidate;
        });

        return $candidates_collection;
    }

    /**
     * @return News[]
     */
    public function get_news_from_ballot(UserBallot $ballot) {
        $candidates = collect($this->get_candidates_from_ballot($ballot));
        
        $candidate_ids = $candidates->pluck('id');
        
        $news_articles = News::with('consolidated_candidate:id,name,office')
          ->whereIn('candidate_id', $candidate_ids)
          ->get();

        return $news_articles;
    }

    /**
     * Select Candidate on Ballot
     */
    public function select_candidate(UserBallot $ballot, ConsolidatedCandidate $candidate) {
        $candidates = $this->get_candidates_from_ballot($ballot);

        $race_candidates = $this->get_candidates_by_race($candidates, $candidate->office);

        $race_candidate_ids = $race_candidates->pluck('id');

        DB::table('user_ballot_candidates')
            ->where('user_ballot_id', $ballot->id)
            ->whereIn('candidate_id', $race_candidate_ids)
            ->delete();

        DB::table('user_ballot_candidates')
            ->insert([
                'user_ballot_id'=>$ballot->id,
                'candidate_id'=>$candidate->id]
            );
    }
    
    /**
     * @param string $state_abbreviation
     * @return ConsolidatedElection[]
     */
    private function get_elections_by_state($state_abbreviation) {
        return ConsolidatedElection::where('state_abbreviation', $state_abbreviation)->get();
    }

    /**
     * @return ConsolidatedCandidate[]
     */
    private function _get_candidates_from_ballot($elections, UserBallot $ballot) {
        $election_candidates = array();

        foreach($elections as $election) {
            $election_candidates = array_merge($election_candidates, 
                $this->get_candidates_from_local($election->candidates, $ballot->county),
                $this->get_candidates_from_congressional_district($election->candidates, $ballot->congressional_district),
                $this->get_candidates_from_state($election->candidates),
                $this->get_candidates_from_state_senate($election->candidates, $ballot->state_legislative_district),
                $this->get_candidates_from_state_house($election->candidates, $ballot->state_house_district),
                $this->get_candidates_from_county($election->candidates, $ballot->county),
                $this->get_candidates_from_city($election->candidates, $ballot->city)
            );
        }

        return $election_candidates;
    }

    /**
     * @param Collection<ConsolidatedCandidate>
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_without_district($candidates_collection) {
        return $candidates_collection
            ->where('district_identifier', null)
            ->where('district_type', '<>', 'City')
            ->toArray();
    }

    private function get_candidates_from_local($candidates_collection, $district_id) {
      return $candidates_collection
            ->where('office_level', 'Local')
            ->filter(function($value, $key) use ($district_id) {
                return strpos($value->district, $district_id) !== false;
            })
            ->toArray();
    }

    private function get_candidates_from_state($candidates_collection) {
      return $candidates_collection
            ->where('office_level', 'State')
            ->where('district_type', 'State')
            ->toArray();
    }

    /**
     * @param Collection<ConsolidatedCandidate>
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_from_congressional_district($candidates_collection, $district_id) {
        if($district_id == null) { return []; }
        
        return $candidates_collection
            ->where('district_type', 'Congress')
            ->where('office_level', 'Federal')
            ->where('district_identifier', $district_id)->toArray();
    }

    /**
     * @param Collection<ConsolidatedCandidate>
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_from_state_senate($candidates_collection, $district_id) {
        if($district_id == null) { return []; }
        
        return $candidates_collection
            ->where('office_level', 'State')
            ->where('district_type', 'State Legislative (Upper)')
            ->where('district_identifier', $district_id)
            ->toArray();
    }

    /**
     * @param Collection<ConsolidatedCandidate>
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_from_state_house($candidates_collection, $district_id) {
        if($district_id == null) { return []; }
        
        return $candidates_collection
            ->where('office_level', 'State')
            ->where('district_type', 'State Legislative (Lower)')
            ->where('district_identifier', $district_id)
            ->toArray();
    }

    /**
     * @param Collection<ConsolidatedCandidate>
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_from_city($candidates_collection, $city) {
        return $candidates_collection
            ->where('district_type', 'City')
            ->where('district', $city)
            ->toArray();
    }

    /**
     * @param Collection<ConsolidatedCandidate>
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_from_county($candidates_collection, $district_id) {
        if($district_id == null) { return []; }
        
        return $candidates_collection
            ->where('office_level', 'Local')
            ->filter(function($value, $key) use ($district_id) {
                return strpos(trim(str_replace("County", "", $value->district_name)), $district_id) !== false;
            })
            ->toArray();
    }

    /**
     * @param Collection<ConsolidatedCandidate> $candidates_collection - Collection of candidates to filter by
     * @param string $office - The office of the race that needs to be sorted by
     * @return Collection<ConsolidatedCandidate>
     */
    private function get_candidates_by_race($candidates_collection, $office) {
        return $candidates_collection
            ->where('office', $office);
    }
}