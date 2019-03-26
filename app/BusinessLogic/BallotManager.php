<?php

namespace App\BusinessLogic;

use Illuminate\Support\Facades\DB;

use App\DataLayer\News;
use App\DataLayer\Ballot\Ballot;
use App\DataLayer\Election\Election;

use App\BusinessLogic\Models\Candidate;
use App\BusinessLogic\Repositories\TweetRepository;
use App\BusinessLogic\Repositories\CandidateRepository;
use App\BusinessLogic\Repositories\ElectionRepository;
use App\BusinessLogic\Repositories\UserBallotCandidateRepository;

use \DateTime;

class BallotManager {

  private $ballot_election_manager;
  private $ballot_candidate_manager;
  private $ballot_news_manager;
  private $ballot_candidate_filter;
  private $candidate_repository;
  private $election_repository;
  private $user_ballot_candidate_repository;
  private $tweet_repository;
  
  private $call_count = 0;

  public function __construct(
    BallotElectionManager $ballot_election_manager,
    BallotCandidateManager $ballot_candidate_manager,
    BallotNewsManager $ballot_news_manager,
    BallotCandidateFilter $ballot_candidate_filter,
    CandidateRepository $candidate_repository,
    ElectionRepository $election_repository,
    UserBallotCandidateRepository $user_ballot_candidate_repository,
    TweetRepository $tweet_repository
  ) {
    $this->ballot_election_manager = $ballot_election_manager;
    $this->ballot_candidate_manager = $ballot_candidate_manager;
    $this->ballot_news_manager = $ballot_news_manager;
    $this->ballot_candidate_filter = $ballot_candidate_filter;
    $this->candidate_repository = $candidate_repository;
    $this->election_repository = $election_repository;
    $this->user_ballot_candidate_repository = $user_ballot_candidate_repository;
    $this->tweet_repository = $tweet_repository;
  }
  
  public function get_elections_from_ballot(Ballot $ballot) {
    $upcoming_elections = $this->get_upcoming_elections_by_ballot($ballot);
    $past_elections = $this->get_last_elections_by_ballot($ballot);

    $elections = [
      "upcoming_elections" => $this->unset_candidates($upcoming_elections),
      "past_elections" => $this->unset_candidates($past_elections)
    ];
    
    return $elections;
  }

  public function get_candidates_from_ballot(Ballot $ballot, $election_type) {
    // get all elections and candidates for the ballot passed in

    if($election_type === 'upcoming') {
      $elections = $this->get_upcoming_elections_by_ballot($ballot);
    } else if ($election_type === 'past') {
      $elections = $this->get_last_elections_by_ballot($ballot);
    } else {
      $elections = $this->get_upcoming_elections_by_ballot($ballot);
    }

    $elections = $this->unset_candidates($elections);

    $elections_candidates = [];
    
    foreach($elections as $election) {
      $candidates = $this->candidate_repository->get_candidates_by_election_id($election->id);
      $elections_candidates = array_merge($elections_candidates, $candidates);
    }

    $filtered_elections_candidates = $this->ballot_candidate_filter->filter_candidates_by_ballot_location($elections_candidates, $ballot);

    $selected_candidate_ids = $this->user_ballot_candidate_repository->get_selected_candidate_ids($ballot->id);

    $this->ballot_candidate_manager->populate_selected_candidates($filtered_elections_candidates, $selected_candidate_ids);

    return $filtered_elections_candidates;
  }

  public function select_candidate(Ballot $ballot, Candidate $candidate) {
    $selected_candidate_ids = $ballot->candidates;
    $elections = $this->get_last_elections_by_ballot($ballot);
    $candidates = $this->get_candidates_from_ballot($ballot, 'upcoming');
    
    $same_race_candidate_ids = $this->ballot_candidate_manager->get_candidate_ids_from_same_race($candidates, $candidate);
    $this->candidate_repository->select_candidate_on_ballot($ballot->id, $same_race_candidate_ids, $candidate->id);
  }

  public function get_news_from_ballot(Ballot $ballot) {
    $candidates = $this->get_candidates_from_ballot($ballot, 'upcoming');

    return $this->ballot_news_manager->get_news_from_ballot($candidates);
  }

  public function get_tweets_from_ballot(Ballot $ballot) {
    $candidates = $this->get_candidates_from_ballot($ballot, 'upcoming');

    $candidates_twitter_handles = collect($candidates)->pluck('twitter_handle')
      ->filter(function($value, $key) {
        return $value !== '' && $value !== null;
      })
      ->toArray();

    $tweets = $this->tweet_repository->get_tweets_by_handles($candidates_twitter_handles);

    return $tweets;
  }

  /**
   * get_last_elections_by_ballot
   *
   * @param Ballot $ballot
   * @return Election[]
   */
  public function get_last_elections_by_ballot(Ballot $ballot) {
    $elections = $this->election_repository->get_last_elections($ballot->state_abbreviation, new DateTime());

    return $this->filter_valid_elections($ballot, $elections);
  }

  /**
   * get_upcoming_elections_by_ballot
   *
   * @param Ballot $ballot
   * @return Election[]
   */
  public function get_upcoming_elections_by_ballot(Ballot $ballot) {
    $elections = $this->election_repository->get_upcoming_elections($ballot->state_abbreviation, new DateTime());
    return $this->filter_valid_elections($ballot, $elections);
  }

  /**
   * get_election_winners
   *
   * @param Election $election
   * @return Candidate[]
   */
  public function get_winners_of_last_elections(Ballot $ballot) {
    $elections = $this->get_last_elections_by_ballot($ballot);

    $election_ids = collect($elections)->pluck('id')->toArray();
    
    $candidates = $this->candidate_repository->get_candidates_by_election_ids($election_ids);
    $representatives = array_filter($candidates, function($candidate) {
      return $candidate->election_status === 'Won';
    });

    return $representatives;
  }

  /**
   * unset_candidates
   *
   * @param Election[] $elections
   * @return Election[]
   * @description Unsets candidates property from all Election elements in the input array
   */
  private function unset_candidates(array $elections) {
    foreach($elections as $election) {
      unset($election->candidates);
    }

    return $elections;
  }

  private function filter_valid_elections($ballot, $elections) {
    $election_ids = collect($elections)->pluck('id')->toArray();

    $candidates = $this->candidate_repository->get_candidates_by_election_ids($election_ids);

    $filtered_elections_candidates = $this->ballot_candidate_filter->filter_candidates_by_ballot_location($candidates, $ballot);

    $relevant_elections = $this->ballot_election_manager->filter_relevant_elections($elections, $filtered_elections_candidates);
    
    return $relevant_elections;
  }
}