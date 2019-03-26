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
    
    // Check to see if the entity find in the database already
    $existing_candidate = $this->find($candidate); // DB Call
    $candidate_fragment_model = CandidateFragment::where('data_source_id', $data_source_id);

    if($candidate->id === null) {
      $candidate_fragment_model = $candidate_fragment_model
        ->where('name', $candidate->name)
        ->where('district', $candidate->district)
        ->first(); // DB Call
    } else {
      $candidate_fragment_model = $candidate_fragment_model
        ->where('candidate_id', $candidate->id)
        ->first(); // DB Call
    }

    if($candidate_fragment_model !== null) {
      // Preserve id so that when the id gets overwritten during conversion, it can be fixed
      $fragment_id = $candidate_fragment_model->id;
      CandidateDTO::convert($candidate, $candidate_fragment_model);
      $candidate_fragment_model->id = $fragment_id;
      
      $candidate_fragment_model->save(); // DB Call
    } else {
      // create new candidate fragment database model
      $candidate_fragment_model = new CandidateFragment();
      $candidate_fragment_model->data_source_id = $data_source_id;
      CandidateDTO::convert($candidate, $candidate_fragment_model);
      
      // save fragment
      $save_successful = $candidate_fragment_model->save(); // DB Call
    }
    
    if($existing_candidate != null) {
      // combine candidate fragments
      $fragments = CandidateFragment::where('name', $candidate_fragment_model->name)
        ->where('district', $candidate_fragment_model->district)
        ->get()
        ->toArray(); // DB Call

      // TODO: Refactor this out to another repo
      // TODO: Ensure that this is being tested correctly. Logic may not be correct
      $priorities = $this->get_priorities(); // DB Call (Once)
        
      $candidate = $this->fragment_combiner->combine($fragments, $priorities);
      $candidate->id = $existing_candidate->id;
      
      CandidateDTO::convert($candidate, $existing_candidate);

      $existing_candidate->save(); // DB Call
      
      CandidateDTO::convert($existing_candidate, $candidate);

      return $candidate;
    } else {
      // create new candidate db model object
      $candidate_model = new CandidateModel();

      // fill in properties from entity data passed in
      CandidateDTO::convert($candidate, $candidate_model);
      // save candidate db model
      $candidate_model->save(); // DB Call

      // Save generated candidate id to candidate_fragment
      $candidate_fragment_model->candidate_id = $candidate_model->id;
      $candidate_fragment_model->save(); // DB Call

      CandidateDTO::convert($candidate_model, $candidate);

      return $candidate;
    }
  }
   
  function find(Candidate $candidate) {
    return CandidateModel::where('name', $candidate->name)
      ->where('district', $candidate->district)
      ->first();
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