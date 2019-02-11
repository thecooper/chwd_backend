<?php

namespace App\DataLayer\Candidate;

use App\BusinessLogic\Models\Candidate;

class CandidateDTO {
  public static function convert($from_model, $to_model) {
    $to_model->id = $from_model->id;
    $to_model->name = $from_model->name;
    $to_model->party_affiliation = $from_model->party_affiliation;
    $to_model->election_status = $from_model->election_status;
    $to_model->office = $from_model->office;
    $to_model->office_level = $from_model->office_level;
    $to_model->is_incumbent = $from_model->is_incumbent;
    $to_model->district_type = $from_model->district_type;
    $to_model->district = $from_model->district;
    $to_model->district_identifier = $from_model->district_identifier;
    $to_model->ballotpedia_url = $from_model->ballotpedia_url;
    $to_model->website_url = $from_model->website_url;
    $to_model->donate_url = $from_model->donate_url;
    $to_model->facebook_profile = $from_model->facebook_profile;
    $to_model->twitter_handle = $from_model->twitter_handle;
    
    if(isset($from_model->election_id) && $from_model->election_id !== null) {
      $to_model->election_id = $from_model->election_id;
    }

    if(isset($from_model->data_source_id) && $from_model->data_source_id !== null) {
      $to_model->data_source_id = $from_model->data_source_id;
    }
  }
}