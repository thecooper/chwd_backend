<?php

namespace App\BusinessLogic\Repositories;

use App\DataLayer\Candidate\ConsolidatedCandidate as CandidateModel;
use App\BusinessLogic\Models\Candidate;

use \Exception;

class CandidateRepository {
  function all() {
    throw new Exception('Not Implemented');
  }
    
  function get($id) {
    throw new Exception('Not Implemented');
  }
    
  function save($entity) {
    throw new Exception('Not Implemented');
  }
    
  function delete($id) {
    throw new Exception('Not Implemented');
  }

  function getAllByElectionId($election_id) {
    return $this->transferAllModels(CandidateModel::where('election_id', $election_id));
  }

  private function transferModel($candidate_model) {
    return Candidate::fromDatabaseModel($candidate_model);
  }
  
  private function transferAllModels($candidate_models) {
    $candidate_array = [];

    foreach($candidate_models as $candidate_model) {
      array_push($candidate_array, $this->transferModel($candidate_model));
    }

    return $candidate_array;
  }
}