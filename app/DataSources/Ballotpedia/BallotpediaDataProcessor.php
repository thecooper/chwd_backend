<?php

namespace App\DataSources\Ballotpedia;

use Illuminate\Support\Facades\Log;

use App\DataSources\FieldMapper;
use App\DataSources\IndexMapping;
use App\DataLayer\BallotpediaCandidates;

use App\BusinessLogic\Models\Election;
use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\ElectionLoader;
use App\BusinessLogic\CandidateLoader;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\Repositories\CandidateRepository;

use DateTime;

class BallotpediaDataProcessor {

  // Dependencies
  private $field_mapper;
  private $election_repository;
  private $candidate_repository;
  private $district_identity_generator;

  // Service State
  private $initialized;
  private $data_source_id;
  private $processed_elections = [];
  
  public function __construct(FieldMapper $field_mapper, ElectionRepository $election_repository, CandidateRepository $candidate_repository, DistrictIdentityGenerator $district_identity_generator) {
    $this->field_mapper = $field_mapper;
    $this->election_repository = $election_repository;
    $this->candidate_repository = $candidate_repository;
    $this->district_identity_generator = $district_identity_generator;
    $this->initialized = false;

    // TODO: extract this to another class that can dynamically determine the mapping to be used
    // Mapping for fields prior to 11/09
    $this->field_mapper->load([
      new IndexMapping(0, 'state'),
      new IndexMapping(1, 'name'),
      new IndexMapping(2, 'first_name'),
      new IndexMapping(3, 'last_name'),
      new IndexMapping(4, 'ballotpedia_url'),
      new IndexMapping(5, 'candidates_id'),
      new IndexMapping(6, 'party_affiliation'),
      new IndexMapping(7, 'race_id'),
      new IndexMapping(8, 'general_election_date'),
      new IndexMapping(9, 'general_runoff_election_date'),
      new IndexMapping(10, 'office_district_id'),
      new IndexMapping(11, 'district_name'),
      new IndexMapping(12, 'district_type'),
      new IndexMapping(13, 'office_level'),
      new IndexMapping(14, 'office'),
      new IndexMapping(15, 'is_incumbent'),
      new IndexMapping(16, 'general_election_status'),
      new IndexMapping(17, 'website_url'),
      new IndexMapping(18, 'facebook_profile'),
      new IndexMapping(19, 'twitter_handle')
    ]);

    // $this->field_mapper->load([
    //   new IndexMapping(0, 'state'),
    //   new IndexMapping(1, 'name'),
    //   new IndexMapping(2, 'first_name'),
    //   new IndexMapping(3, 'last_name'),
    //   new IndexMapping(4, 'ballotpedia_url'),
    //   new IndexMapping(5, 'candidates_id'),
    //   new IndexMapping(6, 'party_affiliation'),
    //   new IndexMapping(7, 'race_id'),
    //   new IndexMapping(8, 'too_close_to_call'),
    //   new IndexMapping(9, 'general_election_date'),
    //   new IndexMapping(10, 'general_runoff_election_date'),
    //   new IndexMapping(11, 'office_district_id'),
    //   new IndexMapping(12, 'district_name'),
    //   new IndexMapping(13, 'district_type'),
    //   new IndexMapping(14, 'office_level'),
    //   new IndexMapping(15, 'office'),
    //   new IndexMapping(16, 'is_incumbent'),
    //   new IndexMapping(17, 'general_election_status'),
    //   new IndexMapping(18, 'website_url'),
    //   new IndexMapping(19, 'facebook_profile'),
    //   new IndexMapping(20, 'twitter_handle')
    // ]);
  }
  
  public function initialize(int $data_source_id) {
    $this->data_source_id = $data_source_id;

    $this->initialized = true;
  }
  
  public function process_fields(array $fields) {
    if(!$this->initialized) {
      throw new \Exception("Unable to process lines because BallotpediaDataProcessor#initialize() has not been called first");
    }

    try {
      $election = $this->process_election($fields);
    } catch (\Exception $ex) {
      throw new \Exception("Unable to save election: {$ex->getMessage()}");
    }

    try {
      $start_candidate_processing = microtime(true);
      
      $this->process_candidate($fields, $election->id);
      
      $end_candidate_processing = microtime(true);
      $timediff = ($end_candidate_processing - $start_candidate_processing) * 1000;
    } catch (\Exception $ex) {
      throw new \Exception("Unable to save candidate: {$ex->getMessage()}", $ex->getCode(), $ex);
    }
  }

  private function process_election(array $fields) {
    $cache_key = $this->generate_election_cache_key($fields);

    if(!array_key_exists($cache_key, $this->processed_elections)) {
      $translated_fields = $this->translate_election_fields($fields);

      $election = new Election();

      ElectionLoader::load($election, $translated_fields);

      // Save election
      $election = $this->election_repository->save($election, $this->data_source_id);

      // Update cache
      $this->processed_elections[$cache_key] = $election;

      return $election;
    }

    return $this->processed_elections[$cache_key];
  }

  private function translate_election_fields(array $fields) {
    $election_name = ElectionNameGenerator::generate(
      $this->field_mapper->get_field($fields, 'state'),
      $this->field_mapper->get_field($fields, 'general_election_date')
    );

    $primary_election_date = $this->field_mapper->get_field($fields, 'primary_election_date');
    $general_election_date = $this->field_mapper->get_field($fields, 'general_election_date');
    $runoff_election_date = $this->field_mapper->get_field($fields, 'general_runoff_election_date');
    
    $primary_election_date_parsed = $primary_election_date === null ? null : date('Y-m-d', strtotime($primary_election_date));
    $general_election_date_parsed = $general_election_date === null ? null : date('Y-m-d', strtotime($general_election_date));
    $runoff_election_date_parsed = $runoff_election_date === null ? null : date('Y-m-d', strtotime($runoff_election_date));

    return [
      'id' => null,
      'election_id' => null,
      'name' => $election_name,
      'state_abbreviation' => $this->field_mapper->get_field($fields, 'state'),
      'primary_election_date' => $primary_election_date_parsed,
      'general_election_date' => $general_election_date_parsed,
      'runoff_election_date' => $runoff_election_date_parsed,
      'data_source_id' => $this->data_source_id,
    ];
  }

  private function process_candidate(array $fields, int $election_id) {
    $candidate_fields = $this->translate_candidate_fields($fields);

    $candidate_fields['election_id'] = $election_id;

    $candidate = new Candidate();

    $candidate_ballotpedia_id = $this->field_mapper->get_field($fields, 'candidates_id');
    $retrieved_candidate_id = $this->get_chwd_candidate_id($candidate_ballotpedia_id);
    
    $candidate->id = $retrieved_candidate_id;
    
    CandidateLoader::load($candidate, $candidate_fields);

    $this->correct_candidate($candidate, $fields);

    $this->candidate_repository->save($candidate, $this->data_source_id);

    if($retrieved_candidate_id === null) {
      $ballotpedia_candidates = new BallotpediaCandidates();
      $ballotpedia_candidates->ballotpedia_candidate_id = $candidate_ballotpedia_id;
      $ballotpedia_candidates->candidate_id = $candidate->id;
      $ballotpedia_candidates->save();
    }
  }
  
  private function correct_candidate(Candidate $candidate, $fields) {
    if($candidate->election_status === 'NULL' || $candidate->election_status === '' || $candidate->election_status === null) {
      Log::channel('import')->warn("Election status for candidate {$candidate->name} was converted from {$candidate->election_status} to 'Unknown'");
      $candidate->election_status = 'Unknown';
    }
    
    if($candidate->party_affiliation === 'NULL' || $candidate->party_affiliation === '' || $candidate->party_affiliation === null) {
      Log::channel('import')->warn("Party affiliation for candidate {$candidate->name} was converted from {$candidate->party_affiliation} to 'Independent'");
      $candidate->party_affiliation = 'Independent';
    }

    if($this->field_mapper->get_field($fields, 'too_close_to_call') === 'general') {
      $candidate->election_status = 'Too Close To Call';
    }
  }

  private function translate_candidate_fields(array $fields) {
    $district_name = $this->field_mapper->get_field($fields, 'district_name');

    return [
      'id' => null,
      'ballotpedia_candidate_id' => $this->field_mapper->get_field($fields, 'candidates_id'),
      'name' => $this->field_mapper->get_field($fields, 'name'),
      'party_affiliation' => $this->field_mapper->get_field($fields, 'party_affiliation'),
      'election_status' => $this->field_mapper->get_field($fields, 'general_election_status') ?: 'On the Ballot',
      'office' => $this->field_mapper->get_field($fields, 'office'),
      'office_level' => $this->field_mapper->get_field($fields, 'office_level'),
      'is_incumbent' => strtolower($this->field_mapper->get_field($fields, 'is_incumbent')) === 'yes',
      'district_type' => $this->field_mapper->get_field($fields, 'district_type'),
      'district' => $district_name,
      'district_identifier' => $this->district_identity_generator->generate($district_name),
      'ballotpedia_url' => $this->field_mapper->get_field($fields, 'ballotpedia_url'),
      'website_url' => $this->field_mapper->get_field($fields, 'website_url'),
      'donate_url' => null,
      'facebook_profile' => $this->field_mapper->get_field($fields, 'facebook_profile'),
      'twitter_handle' => $this->field_mapper->get_field($fields, 'twitter_handle'),
      'election_id' => null,
    ];
  }

  private function get_chwd_candidate_id(int $ballotpedia_candidates_id) {
    $candidate_ids = BallotpediaCandidates::where('ballotpedia_candidate_id', $ballotpedia_candidates_id)->get()->first();
    return $candidate_ids === null ? null : $candidate_ids->candidate_id;
  }

  private function generate_election_cache_key(array $fields) {
    $election_state = $this->field_mapper->get_field($fields, 'state');
    $election_primary_date = $this->field_mapper->get_field($fields, 'primary_election_date') ?: '';
    $election_general_date = $this->field_mapper->get_field($fields, 'general_election_date') ?: '';
    $election_runoff_date = $this->field_mapper->get_field($fields, 'general_runoff_election_date') ?: '';

    return "$election_state$election_primary_date$election_general_date$election_runoff_date";
  }
}

interface IFieldProcessor {
  function process(array $fields);
}