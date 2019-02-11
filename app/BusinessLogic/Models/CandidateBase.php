<?php

namespace App\BusinessLogic\Models;

use App\DataLayer\Candidate\CandidateDTO;

class CandidateBase {
  public $id;
  public $name;
  public $party_affiliation;
  public $election_status;
  public $office;
  public $office_level;
  public $is_incumbent;
  public $district_type;
  public $district;
  public $district_identifier;
  public $ballotpedia_url;
  public $website_url;
  public $donate_url;
  public $facebook_profile;
  public $twitter_handle;
  public $election_id;
}