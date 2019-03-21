<?php

namespace App\BusinessLogic;

use App\DataLayer\Election\ConsolidatedElection;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\Repositories\CandidateRepository;

use \DateTime;

class BallotElectionManager {
  
  private $election_repository;
  
  public function __construct(ElectionRepository $election_repository, CandidateRepository $candidate_repository) {
    $this->election_repository = $election_repository;
  }
  
  /**
   * @param string $state_abbreviation
   * @return App\BusinessLogic\Models\Election[]
   */
  public function get_elections_by_state($state_abbreviation) {
    $elections = $this->election_repository->allByStateWithCandidates($state_abbreviation);
    return $elections;
  }

  public function get_last_elections(string $state_abbreviation, DateTime $date) {
    $elections = $this->election_repository->get_last_elections($state_abbreviation, $date);
    return $elections;
  }

  public function get_upcoming_elections(string $state_abbreviation, DateTime $date) {
    $elections = $this->election_repository->get_upcoming_elections($state_abbreviation, $date);
    return $elections;
  }

  /**
   * filter_relevant_elections
   *
   * @param Election[] $elections
   * @param Candidate[] $relevant_candidates
   * @return Election[]
   */
  public function filter_relevant_elections($elections, $relevant_candidates) {
    return collect($elections)
      ->whereIn('id', collect($relevant_candidates)->pluck('election_id')
      ->unique())
      ->toArray();
  }
}