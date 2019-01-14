<?php

namespace App\BusinessLogic\Models;

use App\DataLayer\Election\ElectionDTO;

class Election extends ElectionBase {
  public $candidates = [];
  
  /**
   * fromDatabaseModel
   *
   * @param App\DataLayer\Election\Election $election_model
   * @return App\BusinessLogic\Models\Election
   */
  public static function fromDatabaseModel($election_model) {
    $election = new Election();
  
    ElectionDTO::convert($election_model, $election);

    return $election;
  }
}