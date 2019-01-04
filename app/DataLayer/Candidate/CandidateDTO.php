<?php

namespace App\DataLayer\Candidate;

use App\BusinessLogic\Models\Candidate;

class CandidateDTO {
  public static function convert($candidate_model, Candidate $candidate) {
    $candidate->id = $candidate_model->id;
    $candidate->name = $candidate_model->name;
    $candidate->election_id = $candidate_model->election_id;
    $candidate->party_affiliation = $candidate_model->party_affiliation;
    $candidate->election_status = $candidate_model->election_status;
    $candidate->office = $candidate_model->office;
    $candidate->office_level = $candidate_model->office_level;
    $candidate->is_incumbent = $candidate_model->is_incumbent;
    $candidate->district_type = $candidate_model->district_type;
    $candidate->district = $candidate_model->district;
    $candidate->district_identifier = $candidate_model->district_identifier;
    $candidate->ballotpedia_url = $candidate_model->ballotpedia_url;
    $candidate->website_url = $candidate_model->website_url;
    $candidate->donate_url = $candidate_model->donate_url;
    $candidate->facebook_profile = $candidate_model->facebook_profile;
    $candidate->twitter_handle = $candidate_model->twitter_handle;
    $candidate->data_source_id = $candidate_model->data_source_id;
  }
}