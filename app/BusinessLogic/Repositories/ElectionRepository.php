<?php

namespace App\BusinessLogic\Repositories;

use App\DataLayer\Election\ElectionFragment;
use App\DataLayer\Election\Election as ElectionModel;
use App\DataLayer\Election\ElectionDTO;
use App\DataLayer\DataSource\DatasourceDTO;
use App\DataLayer\DataSource\DataSourcePriority;

use App\BusinessLogic\ElectionFragmentCombiner;
use App\BusinessLogic\Models\Election;
use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\Models\Datasource;

use \Exception;
use \DateTime;

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
    $election_model = ElectionModel::find($id);
    
    if($election_model === null) {
      return null;
    }

    return $this->transferModel($election_model);
  }

  function save(Election $entity, int $data_source_id) {
    // TODO: refactor so that only dataource->id is passed in since that's all that is necessary

    // Check to see if the entity find in the database already
    $existing_election = $this->find($entity);
    $election_fragment_model = null;
    
    if($entity->id === null) {
      $election_fragment_model = ElectionFragment::where('state_abbreviation', $entity->state_abbreviation)
      ->where('primary_election_date', $entity->primary_election_date)
      ->where('general_election_date', $entity->general_election_date)
      ->where('runoff_election_date', $entity->runoff_election_date)
      ->where('data_source_id', $data_source_id)
      ->first();
    } else {
      $election_fragment_model = ElectionFragment::where('election_id', $entity->id)
        ->where('data_source_id', $data_source_id)
        ->first();
    }
    
    if($election_fragment_model !== null) {
      $fragment_id = $election_fragment_model->id;
      ElectionDTO::convert($entity, $election_fragment_model);
      $election_fragment_model->id = $fragment_id;

      $election_fragment_model->save();
    } else {
      // create new election fragment database model
      $election_fragment_model = new ElectionFragment();
      $election_fragment_model->data_source_id = $data_source_id;
      ElectionDTO::convert($entity, $election_fragment_model);
      // save fragment
      $election_fragment_model->save();
    }

    if($existing_election != null) {
      // combine election fragments
      $fragments = ElectionFragment::where('state_abbreviation', $entity->state_abbreviation)
        ->where('primary_election_date', $entity->primary_election_date)
        ->where('general_election_date', $entity->general_election_date)
        ->where('runoff_election_date', $entity->runoff_election_date)
        ->get()
        ->toArray();

      // TODO: Refactor this out to another repo
      // TODO: Ensure that this is being tested correctly. Logic may not be correct
      $priorities = DataSourcePriority::where('destination_table', 'elections')
        ->get()
        ->sortByDesc('priority')
        ->toArray();
        
      $election = $this->fragment_combiner->combine($fragments, $priorities);
      $election->id = $existing_election->id;
      
      ElectionDTO::convert($election, $existing_election);

      $existing_election->save();

      ElectionDTO::convert($existing_election, $entity);

      return $entity;
    } else {
      // create new election db model object

      $election_model = new ElectionModel();
      // fill in properties from entity data passed in
      ElectionDTO::convert($entity, $election_model);
      // save election db model
      $election_model->save();

      // Save generated election id to election_fragment
      $election_fragment_model->election_id = $election_model->id;
      $election_fragment_model->save();

      ElectionDTO::convert($election_model, $entity);

      return $entity;
    }
  }

  function delete($id) {
    throw new Exception('Not implemented');
  }

  function find(Election $entity) {
    $election_model = ElectionModel::where('state_abbreviation', $entity->state_abbreviation)
      ->where('primary_election_date', $entity->primary_election_date)
      ->where('general_election_date', $entity->general_election_date)
      ->where('runoff_election_date', $entity->runoff_election_date)
      ->first();

    return $election_model;
  }

  function get_last_elections(string $state_abbreviation, DateTime $date) {
    $last_elections = ElectionModel::where('state_abbreviation', $state_abbreviation)
      ->get()
      ->filter(function($election) use ($date) {
        // Filter out upcoming elections
        $general_election_date = new DateTime($election->general_election_date);
        return $general_election_date->getTimestamp() < $date->getTimestamp();
      })
      ->sortByDesc('general_election_date')
      ->sortByDesc('runoff_election_date')
      ->groupBy('general_election_date')
      ->first();
    if ($last_elections === null) {
      return [];
    } else {
      $last_elections = $last_elections->values();
      return $this->transferAllModels($last_elections, true);
    }
  }

  function get_upcoming_elections(string $state_abbreviation, DateTime $date) {
    $upcoming_elections = ElectionModel::where('state_abbreviation', $state_abbreviation)
      ->get()
      ->filter(function($election) use ($date) {
        // Filter out past elections
        $general_election_date = new DateTime($election->general_election_date);
        return $general_election_date->getTimestamp() > $date->getTimestamp();
      })
      ->sortBy('general_election_date')
      ->sortByDesc('runoff_election_date');
    
    return $this->transferAllModels($upcoming_elections, true);
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