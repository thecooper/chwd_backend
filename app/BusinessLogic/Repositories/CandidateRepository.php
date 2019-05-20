<?php

namespace App\BusinessLogic\Repositories;

use Illuminate\Support\Facades\DB;

use App\DataLayer\Candidate\Candidate as CandidateModel;
use App\DataLayer\Candidate\CandidateDTO;
use App\DataLayer\Candidate\CandidateFragment;
use App\DataLayer\DataSource\DataSourcePriority;

use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\Repositories\UserBallotCandidateRepository;
use App\BusinessLogic\CandidateFragmentCombiner;
use App\BusinessLogic\Validation\CandidateValidation;

use \Exception;

class CandidateRepository {

  private $fragment_combiner;
  private $validation;
  private $priorities;

  public function __construct(CandidateFragmentCombiner $fragment_combiner, CandidateValidation $validation) {
    if($fragment_combiner === null) {
      throw new Exception("Dependency for CandidateRepository (CandidateFragmentCombiner) is null");
    }
    
    if($validation === null) {
      throw new Exception("Dependency for CandidateRepository (CandidateValidation) is null");
    }
    
    $this->fragment_combiner = $fragment_combiner;
    $this->validation = $validation;
    $this->priorities = null;
  }
  
  function all() {
    $db_candidates = CandidateModel::all();
    
    return $this->transferAllModels($db_candidates);
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
    $candidate->id = $id;
    return $candidate;
  }
  
  /**
   * get_candidates_by_election_id
   *
   * @param integer $election_id
   * @return Candidate[]
   */  
  function get_candidates_by_election_id(int $election_id) {
    $candidates = CandidateModel::where('election_id', $election_id)->get();
    return $this->transferAllModels($candidates);
  }

  function get_candidates_by_election_ids(array $election_ids) {
    $candidates = CandidateModel::whereIn('election_id', $election_ids)->get();
    return $this->transferAllModels($candidates);
  }
  
  function save(Candidate $candidate, int $data_source_id) {
    // Validate candidate model
    $validation_result = $this->validation->validate($candidate);

    if($validation_result !== true) {
      throw new Exception("Cannot save candidate - $validation_result");
    }
    
    $priorities = $this->get_priorities(); // DB Call (Once)
    
    // Check if candidate already exists in DB. If so, save fragment, then update candidate from fragment combiner
    // Otherwise, save new candidate and then save fragment.
    if($candidate->id !== null) {
      $existing_candidate = CandidateModel::find($candidate->id);

      $existing_candidate_id = $existing_candidate->id;
      $candidate_fragment = CandidateFragment::where('candidate_id', $existing_candidate->id)
        ->where('data_source_id', $data_source_id)->first();

      // Check if fragment is coming from new datasource. If so, save new fragment, otherwise update existing.
      if($candidate_fragment === null) {
        $candidate_fragment = new CandidateFragment();
      }

      CandidateDTO::convert($candidate, $candidate_fragment);
      $candidate_fragment->candidate_id = $candidate->id;
      $candidate_fragment->data_source_id = $data_source_id;
      $candidate_fragment->save();
      
      // combine candidate fragments
      $fragments = CandidateFragment::where('candidate_id', $existing_candidate->id)
        ->get()
        ->toArray(); // DB Call
        
      $candidate = $this->fragment_combiner->combine($fragments, $priorities);
      $candidate->id = $existing_candidate_id;
      CandidateDTO::convert($candidate, $existing_candidate);

      $existing_candidate->id = $existing_candidate_id;
      $existing_candidate->save(); // DB Call

      return $candidate;
    } else {
      // Create new candidate
      $candidate_model = new CandidateModel();
      CandidateDTO::convert($candidate, $candidate_model);
      $candidate_model->save();
      
      // Create and save new candidate fragment
      $candidate_fragment_model = new CandidateFragment();
      CandidateDTO::convert($candidate, $candidate_fragment_model);
      $candidate_fragment_model->candidate_id = $candidate_model->id;
      $candidate_fragment_model->data_source_id = $data_source_id;
      $candidate_fragment_model->save();
      
      CandidateDTO::convert($candidate_model, $candidate);
      $candidate->id = $candidate_model->id;
      return $candidate;
    }
  }
  
  function delete($id) {
    throw new Exception('Not Implemented');
  }

  function getAllByElectionId($candidate_id) {
    $candidate_models = CandidateModel::with('selected')->where('candidate_id', $candidate_id);
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

  private function get_priorities() {
    if($this->priorities === null) {
      $this->priorities = DataSourcePriority::where('destination_table', 'candidates')
      ->get()
      ->sortByDesc('priority')
      ->toArray();
    }

    return $this->priorities;
  }
}