<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

use Illuminate\Http\Request;

use App\BusinessLogic\BallotManager;
use App\BusinessLogic\Repositories\TweetRepository;
use App\BusinessLogic\Repositories\CandidateRepository;

use App\DataLayer\Ballot\Ballot;
use App\DataLayer\Candidate\Candidate;
use App\DataLayer\Election\ConsolidatedElection;

use App\DataSources\NewsAPIDataSource;
use App\DataSources\TwitterDataSource;
use App\Jobs\SelectElectionToProcessNews;
use App\News;

Route::get('repo_test', function(TweetRepository $repo) {
  return response()->json($repo->get_tweets_by_handles(['AOC', 'BetoORourke']), 200);
});

Route::middleware('auth.basic')->group(function () {
    Route::resource('users', 'UsersController')->only('index');

    Route::get('news', function(Request $request) {
      return News::orderByDesc('publish_date')->get();
    });
    
    Route::prefix('users/me')->group(function() {
        Route::get('', 'UsersController@show');

        // TODO: move the saved news functionality to /ballots/{ballot_id}/news/saved
        Route::resource('news', 'UserNewsController')->only('index', 'update', 'destroy');
        
        Route::resource('ballots', 'BallotsController')->except('update');

        Route::prefix('ballots/{ballot}')->group(function () {
          Route::middleware('ballot-valid-user:ballot')->group(function () {
            Route::resource('candidates', 'BallotCandidatesController')->except('store', 'show');
    
            Route::get('candidates/{candidate}/news', function(Request $request, Ballot $ballot, Candidate $candidate) {
                return response()->json(
                  $candidate
                    ->news
                    ->sortByDesc('publish_date')
                    ->flatten()
                    ->take(10)
                    ->map(function($news, $key) use ($candidate) {
                      $news->consolidated_candidate = new stdClass();
                      $news->consolidated_candidate->candidate_id = $candidate->id;
                      $news->consolidated_candidate->office = $candidate->office;
                      $news->consolidated_candidate->name = $candidate->name;
                      return $news;
                    })
                    , 200);
            });
    
            Route::resource('elections', 'BallotElectionsController')->only('index');
    
            Route::resource('representatives', 'BallotElectionWinnersController')->only('index');
            
            Route::get('news', function(Request $request, BallotManager $ballot_manager, Ballot $ballot) {
                $news_articles = $ballot_manager
                  ->get_news_from_ballot($ballot)
                  ->sortByDesc('publish_date')
                  ->flatten()
                  ->take(20);
    
                return response()->json($news_articles, 200);
            });
    
            Route::get('tweets', function(Request $request, BallotManager $ballot_manager, Ballot $ballot) {
              $news_articles = collect($ballot_manager
                ->get_tweets_from_ballot($ballot))
                ->sortByDesc('created_at')
                ->flatten()
                ->take(100);
    
              return response()->json($news_articles, 200);
            });
          });
        });
    });
  });
  
Route::resource('elections', 'ElectionsController')->only('index', 'show');
Route::resource('candidates', 'CandidatesController')->only('index', 'show');

Route::get('candidates/{candidate_id}/tweets', function(Request $request, $candidate_id, CandidateRepository $candidate_repository, TweetRepository $tweet_repository) {
  $candidate = $candidate_repository->get($candidate_id);

  $tweets = $tweet_repository->get_tweets_by_handles([$candidate->twitter_handle]);

  return response()->json($tweets, 200);
});

// User Registration
Route::post('users', 'UsersController@store');