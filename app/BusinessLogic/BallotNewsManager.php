<?php

namespace App\BusinessLogic;

use App\DataLayer\News;
use App\BusinessLogic\Repositories\NewsRepository;

class BallotNewsManager {

  private $news_repository;
  
  public function __construct(NewsRepository $news_repository) {
    $this->news_repository = $news_repository;
  }
  
  /**
   * @param Candidates[]
   * @return News[]
   */
  public function get_news_from_ballot(array $candidates) {
    $candidate_ids = collect($candidates)->pluck('id')->toArray();
    
    $news_articles = $this->news_repository->get_for_candidates($candidate_ids);

    return $news_articles;
  }
}