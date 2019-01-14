<?php

namespace App\BusinessLogic\Repositories;

use App\DataLayer\Election\ElectionFragment;
use App\DataLayer\Election\Election as ElectionModel;
use App\DataLayer\Election\ElectionDTO;
use App\DataLayer\Election\ElectionFragmentDTO;
use App\DataLayer\DataSource\DatasourceDTO;
use App\DataLayer\DataSource\DataSourcePriority;

use App\BusinessLogic\ElectionFragmentCombiner;
use App\BusinessLogic\Models\Election;
use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\Models\Datasource;

use \Exception;

class ElectionRepository {

  private $fragment_combiner;
  
  public function __construct(ElectionFragmentCombiner $fragment_combiner) {
    $this->fragment_combiner = $fragment_combiner;
  }
  
  function all() {
    $db_elections = ElectionModel::all();
    
    return $this->transferAllModels($db_elections);
  }

  function allByState($state_abbreviation) {
    $election_models = ElectionModel::where('state_abbreviation', $state_abbreviation)->get();

    return $this->transferAllModels($election_models);
  }

  function allByStateWithCandidates($state_abbreviation) {
    $election_models = ElectionModel::where('state_abbreviation', $state_abbreviation)->get();
    return $this->transferAllModels($election_models, true);
  }

  function get($id) {
    return $this->transferModel(ElectionModel::get($id));
  }

  function save(Election $entity, DataSource $datasource) {
    // Determine existing election id
    $election_already_exists = $this->exists($entity);
    
    // create new election fragment database model
    $election_fragment_model = new ElectionFragment();
    // save election fragment to database

    $election_fragment_model->id = $entity->id;
    $election_fragment_model->name = $entity->name;
    $election_fragment_model->state_abbreviation = $entity->state_abbreviation;
    $election_fragment_model->primary_election_date = $entity->primary_election_date;
    $election_fragment_model->general_election_date = $entity->general_election_date;
    $election_fragment_model->runoff_election_date = $entity->runoff_election_date;
    $election_fragment_model->data_source_id = $datasource->id;
    $election_fragment_model->election_id = null;

    // save fragment
    $election_fragment_model->save();

    if($election_already_exists) {
      // combine election fragments
      $fragments = ElectionFragment::where('state_abbreviation', $entity->state_abbreviation)
        ->where('primary_election_date', $entity->primary_election_date)
        ->where('general_election_date', $entity->general_election_date)
        ->where('runoff_election_date', $entity->runoff_election_date)
        ->get()
        ->toArray();

      // TODO: Refactor this out to another repo
      $priorities = DataSourcePriority::where('destination_table', 'elections')
        ->get()
        ->sortByDesc('priority')
        ->toArray();
        
      $election = $this->fragment_combiner->combine($fragments, $priorities);
      
      $election_model = new ElectionModel();
      ElectionDTO::convert($election, $election_model);

      $election_model->save();

      return $election_model;
    } else {
      // create new election db model object
      $election_model = new ElectionModel();
      // fill in properties from entity data passed in
      ElectionDTO::convert($entity, $election_model);
      // save election db model
      $election_model->save();

      return $election_model;
    }
  }

  function delete($id) {
    throw new Exception('Not implemented');
  }

  function exists(Election $entity) {
    $election_model = ElectionModel::where('state_abbreviation', $entity->state_abbreviation)
      ->where('primary_election_date', $entity->primary_election_date)
      ->where('general_election_date', $entity->general_election_date)
      ->where('runoff_election_date', $entity->runoff_election_date)
      ->first();

    $election_model == null;

    return $election_model;
  }

  private function transferModel($election_model, $include_candidates = false) {
    $election = Election::fromDatabaseModel($election_model);

    if($include_candidates) {
      foreach($election_model->candidates as $candidate_model) {
        $candidate = Candidate::fromDatabaseModel($candidate_model);
        array_push($election->candidates, $candidate);
      }
    }

    return $election;
  }
  
  private function transferAllModels($election_models, $include_candidates = false) {
    $election_array = [];
    
    foreach($election_models as $election_model) {
      array_push($election_array, $this->transferModel($election_model, $include_candidates));
    }

    return $election_array;
  }
}