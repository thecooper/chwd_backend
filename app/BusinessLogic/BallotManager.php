<?php

namespace App\BusinessLogic;

use Illuminate\Support\Facades\DB;

use App\DataLayer\Ballot\Ballot;
use App\DataLayer\Candidate\ConsolidatedCandidate;
use App\DataLayer\Election\ConsolidatedElection;
use App\DataLayer\News;

class BallotManager {

  private $ballot_election_manager;
  private $ballot_candidate_manager;
  private $ballot_news_manager;
  private $ballot_candidate_filter;

  public function __construct(
    BallotElectionManager $ballot_election_manager,
    BallotCandidateManager $ballot_candidate_manager,
    BallotNewsManager $ballot_news_manager,
    BallotCandidateFilter $ballot_candidate_filter
  ) {
    $this->ballot_election_manager = $ballot_election_manager;
    $this->ballot_candidate_manager = $ballot_candidate_manager;
    $this->ballot_news_manager = $ballot_news_manager;
    $this->ballot_candidate_filter = $ballot_candidate_filter;
  }
  
  public function get_elections_from_ballot(Ballot $ballot) {
    $elections = $this->ballot_election_manager->get_elections_by_state($ballot->state_abbreviation);
    $elections_candidates = $this->ballot_candidate_manager->get_candidates_from_elections($elections);
    $filtered_elections_candidates = $this->ballot_candidate_filter->filter_candidates_by_ballot_location($elections_candidates, $ballot);

    $relevant_elections = $this->ballot_election_manager->filter_relevant_elections($elections, $filtered_elections_candidates);

    foreach($relevant_elections as $election) {
        unset($election->candidates);
    }

    return $relevant_elections;
  }

  public function get_candidates_from_ballot(Ballot $ballot) {
    $elections = $this->ballot_election_manager->get_elections_by_state($ballot->state_abbreviation);
    $elections_candidates = $this->ballot_candidate_manager->get_candidates_from_elections($elections);
    $filtered_elections_candidates = $this->ballot_candidate_filter->filter_candidates_by_ballot_location($elections_candidates, $ballot);

    return $filtered_elections_candidates;
  }

  public function select_candidate(Ballot $ballot, ConsolidatedCandidate $candidate) {
    $selected_candidate_ids = $ballot->candidates;
    $candidates = $this->get_candidates_from_ballot($ballot);
    
    $same_race_candidate_ids = $this->ballot_candidate_manager->get_candidate_ids_from_same_race($candidates, $candidate);
    $this->ballot_candidate_manager->update_candidate_table($ballot->id, $same_race_candidate_ids, $candidate->id);
  }

  public function get_news_from_ballot(Ballot $ballot) {
    $candidates = $this->get_candidates_from_ballot($ballot);

    return $this->ballot_news_manager->get_news_from_ballot($candidates);
  }

}