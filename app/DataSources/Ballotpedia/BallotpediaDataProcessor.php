<?php

namespace App\DataSources\Ballotpedia;

use App\DataSources\FieldMapper;
use App\DataSources\IndexMapping;

use App\BusinessLogic\Models\Election;
use App\BusinessLogic\ElectionLoader;
use App\BusinessLogic\Repositories\ElectionRepository;

class BallotpediaDataProcessor implements IFieldProcessor {

  // Dependencies
  private $field_mapper;
  private $election_repository;

  // Service State
  private $initialized;
  private $data_source_id;
  private $processed_elections = [];
  
  public function __construct(FieldMapper $field_mapper, ElectionRepository $election_repository) {
    $this->field_mapper = $field_mapper;
    $this->election_repository = $election_repository;
    $this->initialized = false;
  }
  
  public function initialize(int $data_source_id) {
    $this->data_source_id = $data_source_id;

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

    $this->initialized = true;
  }
  
  public function process(array $fields) {
    if(!$this->initialized) {
      throw new \Exception("Unable to process lines because BallotpediaDataProcessor#initialize() has not been called first");
    }

    $this->process_election($fields);

    $this->process_candidate($fields);
  }

  private function process_election(array $fields) {
    $cache_key = $this->generate_election_cache_key($fields);

    if(!in_array($cache_key, $this->processed_elections)) {
      $translated_fields = $this->translate_election_fields($fields);

      $election = new Election();

      ElectionLoader::load($election, $translated_fields);

      // Save election
      $this->election_repository->save($election);

      // Update cache
      array_push($this->processed_elections, $cache_key);
    }
  }

  private function translate_election_fields(array $fields) {
    $election_name = ElectionNameGenerator::generate(
      $this->field_mapper->get_field($fields, 'state'),
      $this->field_mapper->get_field($fields, 'general_election_date')
    );

    return [
      'id' => null,
      'election_id' => null,
      'name' => $election_name,
      'state_abbreviation' => $this->field_mapper->get_field($fields, 'state'),
      'primary_election_date' => $this->field_mapper->get_field($fields, 'primary_election_date'),
      'general_election_date' => $this->field_mapper->get_field($fields, 'general_election_date'),
      'runoff_election_date' => $this->field_mapper->get_field($fields, 'general_runoff_election_date'),
      'data_source_id' => $this->data_source_id,
    ];
  }

  private function process_candidate(array $fields) {

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