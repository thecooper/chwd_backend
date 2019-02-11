<?php

namespace App\BusinessLogic\Models;

use App\DataLayer\Candidate\CandidateDTO;

class Candidate extends CandidateBase {
  public $selected = false;

  /**
   * fromDatabaseModel
   *
   * @param App\DataLayer\Candidate\Candidate $candidate_model
   * @return App\BusinessLogic\Models\Candidate
   */
  public static function fromDatabaseModel($candidate_model) {
    $candidate = new self();
  
    CandidateDTO::convert($candidate_model, $candidate);

    return $candidate;
  }
}