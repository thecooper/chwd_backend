<?php

namespace App\BusinessLogic\Models;

use App\DataLayer\Election\ElectionDTO;

class Election {
  public $id;
  public $name;
  public $state_abbreviation;
  public $primary_election_date;
  public $general_election_date;
  public $runoff_election_date;
  public $data_source_id;

  public $candidates = [];
  
  public static function fromDatabaseModel($election_model) {
    $election = new Election();
  
    ElectionDTO::convert($election_model, $election);

    return $election;
  }
}