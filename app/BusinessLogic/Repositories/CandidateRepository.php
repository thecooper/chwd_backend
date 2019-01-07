<?php

namespace App\BusinessLogic\Repositories;

use Illuminate\Support\Facades\DB;

use App\DataLayer\Candidate\ConsolidatedCandidate as CandidateModel;
use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\Repositories\UserBallotCandidateRepository;

use \Exception;

class CandidateRepository {

  private $user_ballot_candidate_repository;

  public function __construct(UserBallotCandidateRepository $user_ballot_candidate_repository) {
    $this->user_ballot_candidate_repository = $user_ballot_candidate_repository;
  }
  
  function all() {
    throw new Exception('Not Implemented');
  }
    
  /**
   * get
   *
   * @param int $id
   * @return Candidate
   */
  function get($id) {
    $candidate_model = CandidateModel::find($id);
    $candidate = $this->transferModel($candidate_model);
    return $candidate;
  }
    
  function save($entity) {
    throw new Exception('Not Implemented');
  }
    
  function delete($id) {
    throw new Exception('Not Implemented');
  }

  function getAllByElectionId($election_id) {
    $candidate_models = CandidateModel::with('selected')->where('election_id', $election_id);
    return $this->transferAllModels($candidate_models);
  }

  /**
   * select_candidate_on_ballot
   *
   * @param int $ballot_id
   * @param int[] $race_candidate_ids
   * @param int $candidate_id
   * @return void
   */
  public function select_candidate_on_ballot($ballot_id, $race_candidate_ids, $candidate_id) {
    DB::table('user_ballot_candidates')
        ->where('user_ballot_id', $ballot_id)
        ->whereIn('candidate_id', $race_candidate_ids)
        ->delete();
  
    DB::table('user_ballot_candidates')
        ->insert([
            'user_ballot_id'=>$ballot_id,
            'candidate_id'=>$candidate_id]
        );
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