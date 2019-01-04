<?php

namespace App\BusinessLogic;

use App\DataLayer\Election\ConsolidatedElection;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\Repositories\CandidateRepository;

class BallotElectionManager {
  
  private $election_repository;
  
  public function __construct(ElectionRepository $election_repository, CandidateRepository $candidate_repository) {
    $this->election_repository = $election_repository;
    // $this->candidate_repository = $candidate_repository;
  }
  
  /**
   * @param string $state_abbreviation
   * @return ConsolidatedElection[]
   */
  public function get_elections_by_state($state_abbreviation) {
    $elections = $this->election_repository->allByStateWithCandidates($state_abbreviation);
    // return ConsolidatedElection::where('state_abbreviation', $state_abbreviation)->get();
    return $elections;
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