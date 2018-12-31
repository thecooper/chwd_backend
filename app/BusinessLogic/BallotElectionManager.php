<?php

namespace App\BusinessLogic;

use App\DataLayer\Election\ConsolidatedElection;

class BallotElectionManager {
  
  /**
   * @param string $state_abbreviation
   * @return ConsolidatedElection[]
   */
  public function get_elections_by_state($state_abbreviation) {
    return ConsolidatedElection::where('state_abbreviation', $state_abbreviation)->get();
  }

  /**
   * filter_relevant_elections
   *
   * @param Collection<Election> $elections
   * @param Collection<Candidate> $relevant_candidates
   * @return Collection<Election>
   */
  public function filter_relevant_elections($elections, $relevant_candidates) {
    return $elections->whereIn('id', $relevant_candidates->pluck('election_id')->unique());
  }
}