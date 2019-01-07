<?php

namespace App\BusinessLogic;

use App\DataLayer\News;

class BallotNewsManager {
  /**
   * @param Candidates[]
   * @return News[]
   */
  public function get_news_from_ballot(array $candidates) {
    $candidate_ids = collect($candidates)->pluck('id');
    
    $news_articles = News::with('consolidated_candidate:id,name,office')
      ->whereIn('candidate_id', $candidate_ids)
      ->get();

    return $news_articles;
  }
}