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

use App\News;
use App\DataLayer\Ballot\Ballot;
use App\DataSources\NewsAPIDataSource;
use App\DataSources\Ballotpedia_CSV_File_Source;
use App\BusinessLogic\BallotManager;
use App\DataLayer\Election\ConsolidatedElection;
use App\DataLayer\Candidate\ConsolidatedCandidate;
use App\Jobs\SelectElectionToProcessNews;
use App\DataSources\TwitterDataSource;
use App\BusinessLogic\Serializer\TweetJsonSerializer;

// Route::get('import', 'ImportController@show'); // Importing now done through cli: 'php artisan import'

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

        Route::resource('ballots/{ballot_id}/candidates', 'BallotCandidatesController')->except('store', 'show')->middleware('ballot-valid-user:ballot_id');

        Route::get('ballots/{ballot}/candidates/{candidate}/news', function(Request $request, Ballot $ballot, ConsolidatedCandidate $candidate) {
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
        })->middleware('ballot-valid-user:ballot');

        Route::resource('ballots/{ballot}/elections', 'BallotElectionsController')->only('index')->middleware('ballot-valid-user:ballot');

        Route::get('ballots/{ballot}/news', function(Request $request, BallotManager $ballot_manager, Ballot $ballot) {
            $news_articles = $ballot_manager
              ->get_news_from_ballot($ballot)
              ->sortByDesc('publish_date')
              ->flatten()
              ->take(20);

            return response()->json($news_articles, 200);
        })->middleware('ballot-valid-user:ballot');

        Route::get('ballots/{ballot}/tweets', function(Request $request, Ballot $ballot) {
            return response()->json([], 200);
        })->middleware('ballot-valid-user:ballot');
    });

    // Route::resource('elections', 'ElectionsController')->only('index', 'show');
    // Route::get('elections/{id}/races', 'ElectionsController@races');
    // Route::get('elections/{id}/candidates', 'ElectionsController@election_candidates');

});

Route::resource('candidates', 'CandidatesController')->only('index', 'show');

// User Registration
Route::post('users', 'UsersController@store');

Route::get('elections/{election_id}', function($election_id) {
    (new SelectCandidatesToProcessNews())->handle();
});