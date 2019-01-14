<?php

namespace App\BusinessLogic\Models;

class ElectionBase {
  public $id;
  public $name;
  public $state_abbreviation;
  public $primary_election_date;
  public $general_election_date;
  public $runoff_election_date;
}