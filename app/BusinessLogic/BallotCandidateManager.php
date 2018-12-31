<?php

namespace App\BusinessLogic;

use App\DataLayer\Candidate\ConsolidatedCandidate;
use App\DataLayer\Ballot\Ballot;

class BallotCandidateManager {

  private $candidate_filter;
  private $ballot_election_manager;
  
  public function __construct(BallotElectionManager $ballot_election_manager, BallotCandidateFilter $filter) {
    $this->ballot_election_manager = $ballot_election_manager;
    $this->candidate_filter = $filter;
  }
  
  // /**
  //  * @return ConsolidatedCandidate[]
  //  */
  // public function get_candidates_from_ballot(Ballot $ballot, $elections) {
  //   $selected_candidate_ids = $ballot->candidates;
  //   $candidates_collection = collect($this->get_candidates_from_elections($elections, $ballot));
    
  //   $candidates_collection = $candidates_collection
  //     ->map(function($candidate) use ($selected_candidate_ids) {
  //       $candidate["selected"] = $selected_candidate_ids->contains($candidate["id"]);
  //       return $candidate;
  //     });

  //   // $relevant_elections = $this->ballot_election_manager->filter_relevant_elections($elections, $ballot_candidate_collection);

  //   return $candidates_collection;
  // }

  /**
   * Select Candidate on Ballot
   */
  public function get_candidate_ids_from_same_race($candidates, ConsolidatedCandidate $candidate) {
    return $this->get_candidates_from_similar_race($candidates, $candidate)->pluck('id');
  }

  public function get_candidates_from_similar_race($candidates, $candidate) {
    $candidates_collection
        // ->where('office_level', $candidate->office_level)
        // ->where('district_type', $candidate->district_type)
        ->where('office', $candidate->office);
  }
  
  public function update_candidate_table($ballot_id, $race_candidate_ids, $candidate_id) {
    DB::table('user_ballot_candidates')
        ->where('user_ballot_id', $ballot_id)
        ->whereIn('candidate_id', $race_candidate_ids)
        ->delete();
  
    DB::table('user_ballot_candidates')
        ->insert([
            'user_ballot_id'=>$ballot_id,
            'candidate_id'=>$candidate_id]
        );
  }

  // /**
  //    * @return ConsolidatedCandidate[]
  //    */
  //   public function get_candidates_from_elections($elections, Ballot $ballot) {
  //     $election_candidates = array();

  //     foreach($elections as $election) {
  //         $election_candidates = array_merge($election_candidates, 
  //             $this->candidate_filter->get_candidates_from_local($election->candidates, $ballot->county),
  //             $this->candidate_filter->get_candidates_from_congressional_district($election->candidates, $ballot->congressional_district),
  //             $this->candidate_filter->get_candidates_from_state($election->candidates),
  //             $this->candidate_filter->get_candidates_from_state_senate($election->candidates, $ballot->state_legislative_district),
  //             $this->candidate_filter->get_candidates_from_state_house($election->candidates, $ballot->state_house_district),
  //             $this->candidate_filter->get_candidates_from_county($election->candidates, $ballot->county),
  //             $this->candidate_filter->get_candidates_from_city($election->candidates, $ballot->city)
  //         );
  //     }

  //     return $election_candidates;
  // }

  public function get_candidates_from_elections($elections) {
    $election_candidates = array();

      foreach($elections as $election) {
          $election_candidates = array_merge($election_candidates, $election->candidates->toArray());
      }

      return collect($election_candidates);
  }
}