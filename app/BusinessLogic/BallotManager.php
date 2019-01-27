<?php

namespace App\BusinessLogic;

use Illuminate\Support\Facades\DB;

use App\DataLayer\Ballot\Ballot;
use App\BusinessLogic\Models\Candidate;
use App\DataLayer\Election\ConsolidatedElection;
use App\DataLayer\News;
use App\BusinessLogic\Repositories\CandidateRepository;
use App\BusinessLogic\Repositories\UserBallotCandidateRepository;
use App\BusinessLogic\Repositories\TweetRepository;

class BallotManager {

  private $ballot_election_manager;
  private $ballot_candidate_manager;
  private $ballot_news_manager;
  private $ballot_candidate_filter;
  private $candidate_repository;
  private $user_ballot_candidate_repository;
  private $tweet_repository;
  
  private $call_count = 0;

  public function __construct(
    BallotElectionManager $ballot_election_manager,
    BallotCandidateManager $ballot_candidate_manager,
    BallotNewsManager $ballot_news_manager,
    BallotCandidateFilter $ballot_candidate_filter,
    CandidateRepository $candidate_repository,
    UserBallotCandidateRepository $user_ballot_candidate_repository,
    TweetRepository $tweet_repository
  ) {
    $this->ballot_election_manager = $ballot_election_manager;
    $this->ballot_candidate_manager = $ballot_candidate_manager;
    $this->ballot_news_manager = $ballot_news_manager;
    $this->ballot_candidate_filter = $ballot_candidate_filter;
    $this->candidate_repository = $candidate_repository;
    $this->user_ballot_candidate_repository = $user_ballot_candidate_repository;
    $this->tweet_repository = $tweet_repository;
  }
  
  public function get_elections_from_ballot(Ballot $ballot) {
    // get all elections and candidates for the ballot passed in
    list($elections, $elections_candidates) = $this->get_ballot_elections_and_candidates($ballot);
    
    $filtered_elections_candidates = $this->ballot_candidate_filter->filter_candidates_by_ballot_location($elections_candidates, $ballot);

    $relevant_elections = $this->ballot_election_manager->filter_relevant_elections($elections, $filtered_elections_candidates);

    // Remove candidates 
    foreach($relevant_elections as $election) {
        unset($election->candidates);
    }

    return $relevant_elections;
  }

  public function get_candidates_from_ballot(Ballot $ballot) {
    // get all elections and candidates for the ballot passed in
    list($elections, $elections_candidates) = $this->get_ballot_elections_and_candidates($ballot);

    // Filter the candidates that are relevant to the user based on the ballot's information. The less specific the ballot location, the less canddiates apply to that ballot.
    $filtered_elections_candidates = $this->ballot_candidate_filter->filter_candidates_by_ballot_location($elections_candidates, $ballot);

    // Get all of the candidates selected in the current ballot
    $selected_candidate_ids = $this->user_ballot_candidate_repository->get_selected_candidate_ids($ballot->id);

    // Set the selected status of each candidate
    $this->ballot_candidate_manager->populate_selected_candidates($filtered_elections_candidates, $selected_candidate_ids);

    return $filtered_elections_candidates;
  }

  public function select_candidate(Ballot $ballot, Candidate $candidate) {
    $selected_candidate_ids = $ballot->candidates;
    $candidates = $this->get_candidates_from_ballot($ballot);
    $same_race_candidate_ids = $this->ballot_candidate_manager->get_candidate_ids_from_same_race($candidates, $candidate);
    $this->candidate_repository->select_candidate_on_ballot($ballot->id, $same_race_candidate_ids, $candidate->id);
  }

  public function get_news_from_ballot(Ballot $ballot) {
    $candidates = $this->get_candidates_from_ballot($ballot);

    return $this->ballot_news_manager->get_news_from_ballot($candidates);
  }

  public function get_tweets_from_ballot(Ballot $ballot) {
    $candidates = $this->get_candidates_from_ballot($ballot);

    $candidates_twitter_handles = collect($candidates)->pluck('twitter_handle')
      ->filter(function($value, $key) {
        return $value !== '';
      })
      ->toArray();

    $tweets = $this->tweet_repository->get_tweets_by_handles($candidates_twitter_handles);

    return $tweets;
  }
  
  private function get_ballot_elections_and_candidates(Ballot $ballot) {
    $elections = $this->ballot_election_manager->get_elections_by_state($ballot->state_abbreviation);
    $elections_candidates = $this->ballot_candidate_manager->get_candidates_from_elections($elections);

    return array($elections, $elections_candidates);
  }

}