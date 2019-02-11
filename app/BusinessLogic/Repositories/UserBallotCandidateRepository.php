<?php

namespace App\BusinessLogic\Repositories;

use App\DataLayer\UserBallotCandidate;

use \Exception;

class UserBallotCandidateRepository implements Repository {
  function all() {
    throw new Exception("Not Implemented");
  }

  function get($id) {
    throw new Exception("Not Implemented");
  }

  function save($entity) {
    throw new Exception("Not Implemented");
  }

  function delete($id) {
    throw new Exception("Not Implemented");
  }

  function candidate_is_selected($ballot_id, $candidate_id) {
    return UserBallotCandidate::where('user_ballot_id', $ballot_id)->where('candidate_id', $candidate_id)->get()->count() === 1;
  }
  
  /**
   * get_selected_candidate_ids
   *
   * @param int $ballot_id
   * @return int[]
   */
  function get_selected_candidate_ids($ballot_id) {
    $user_ballot_candidates = UserBallotCandidate::where('user_ballot_id', $ballot_id)
      ->get()
      ->toArray();
    return $user_ballot_candidates;
  }
}