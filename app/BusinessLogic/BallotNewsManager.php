<?php

namespace App\BusinessLogic;

use App\DataLayer\News;

class BallotNewsManager {
  /**
   * @return News[]
   */
  public function get_news_from_ballot($candidates) {
    $candidate_ids = $candidates->pluck('id');
    
    $news_articles = News::with('consolidated_candidate:id,name,office')
      ->whereIn('candidate_id', $candidate_ids)
      ->get();

    return $news_articles;
  }
}