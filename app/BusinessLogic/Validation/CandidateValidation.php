<?php

namespace App\BusinessLogic\Validation;

use App\BusinessLogic\Models\Candidate;

class CandidateValidation {
  public function validate(Candidate $candidate) {
    if(!$this->validate_election_status($candidate->election_status)) {
      return "Validation failed for election_status for value {$candidate->election_status}";
    } else {
      return true;
    }
  }

  private function validate_election_status(string $election_status) {
    return
      $election_status === 'On the Ballot' ||
      $election_status === 'Lost' ||
      $election_status === 'Won' ||
      $election_status === 'Withdrew' ||
      $election_status === 'Disqualified' ||
      $election_status === 'Advanced' ||
      $election_status === 'Unknown';
  }
}