<?php

namespace App\BusinessLogic;

use App\BusinessLogic\Models\Candidate;
use App\DataLayer\Ballot\Ballot;

class BallotCandidateManager {

  private $candidate_filter;
  private $ballot_election_manager;
  
  public function __construct(BallotElectionManager $ballot_election_manager, BallotCandidateFilter $filter) {
    $this->ballot_election_manager = $ballot_election_manager;
    $this->candidate_filter = $filter;
  }

  /**
   * get_candidate_ids_from_same_race
   * 
   * @param Candidate[] $candidates
   * @param Candidate $candidate
   */
  public function get_candidate_ids_from_same_race(array $candidates, Candidate $candidate) {
    $other_candidates = $this->get_candidates_from_similar_race($candidates, $candidate);
    return collect($other_candidates)->pluck('id');
  }

  /**
   * get_candidates_from_similar_race
   *
   * @param Candidate[] $candidates
   * @param Candidate $candidate
   * @return Candidate[]
   */
  public function get_candidates_from_similar_race($candidates, $candidate) {
    return collect($candidates)
        // ->where('office_level', $candidate->office_level)
        // ->where('district_type', $candidate->district_type)
        ->where('office', $candidate->office)
        ->toArray();
  }

  /**
   * get_candidates_from_elections
   *
   * @param Elections[] $elections
   * @return Candidate[]
   */
  public function get_candidates_from_elections($elections) {
    $election_candidates = array();

    foreach($elections as $election) {
      $election_candidates = array_merge($election_candidates, $election->candidates);
    }

    return $election_candidates;
  }

  /**
   * populate_selected_candidates
   *
   * @param array $candidates
   * @param array $selected_candidate_ids
   * @return void
   */
  public function populate_selected_candidates(array $candidates, array $selected_candidate_ids) {
    foreach($candidates as $candidate) {
      $candidate->selected = in_array($candidate->id, $selected_candidate_ids);
    }
  }
}