<?php

namespace App\DataLayer\Election;

use App\BusinessLogic\Models\Election;
use App\DataLayer\Election\Election as ElectionModel;

class ElectionFragmentDTO {

  /**
   * convert
   *
   * @param any $from_model
   * @param any $to_model
   * @return void
   */
  public static function convert($from_model, $to_model, $transfer_null_values = true) {
    $to_model->id = $from_model->id;
    $to_model->name = $from_model->name;
    $to_model->state_abbreviation = $from_model->state_abbreviation;
    $to_model->primary_election_date = $from_model->primary_election_date;
    $to_model->general_election_date = $from_model->general_election_date;
    $to_model->runoff_election_date = $from_model->runoff_election_date;
    $to_model->data_source_id = $from_model->data_source_id;
    $to_model->election_id = $from_model->election_id;
  }
}