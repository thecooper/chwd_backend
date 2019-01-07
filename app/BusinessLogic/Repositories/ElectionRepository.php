<?php

namespace App\BusinessLogic\Repositories;

use App\DataLayer\Election\ConsolidatedElection;
use App\BusinessLogic\Models\Election;
use App\BusinessLogic\Models\Candidate;

use \Exception;

class ElectionRepository implements Repository {
  function all() {
    $db_elections = ElectionModel::all();
    
    return $this->transferAllModels($db_elections);
  }

  function allByState($state_abbreviation) {
    $election_models = ConsolidatedElection::where('state_abbreviation', $state_abbreviation)->get();

    return $this->transferAllModels($election_models);
  }

  function allByStateWithCandidates($state_abbreviation) {
    $election_models = ConsolidatedElection::where('state_abbreviation', $state_abbreviation)->get();
    return $this->transferAllModels($election_models, true);
  }

  function get($id) {
    return $this->transferModel(ElectionModel::get($id));
  }

  function save($entity) {
    throw new Exception('Not implemented');
  }

  function delete($id) {
    throw new Exception('Not implemented');
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