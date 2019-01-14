<?php

namespace App\BusinessLogic\Repositories;

use App\DataLayer\News;

class NewsRepository {

  public function get_for_candidates(array $candidate_ids) {
    return News::whereIn('candidate_id', $candidate_ids)
      ->get();
  }
}