<?php

namespace App\DataLayer\Election;

class ElectionDTO {
  public static function convert($election_model, $election) {
    $election->id = $election_model->id;
    $election->name = $election_model->name;
    $election->state_abbreviation = $election_model->state_abbreviation;
    $election->primary_election_date = $election_model->primary_election_date;
    $election->general_election_date = $election_model->general_election_date;
    $election->runoff_election_date = $election_model->runoff_election_date;
  }
}