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
use App\UserBallot;
use App\DataSources\NewsAPIDataSource;
use App\DataSources\Ballotpedia_CSV_File_Source;
use App\Models\BallotManager;
use App\Models\Election\ConsolidatedElection;
use App\Models\Candidate\ConsolidatedCandidate;
use App\Jobs\SelectElectionToProcessNews;

// Route::get('import', 'ImportController@show'); // Importing now done through cli: 'php artisan import'

Route::middleware('auth.basic')->group(function () {
    Route::resource('users', 'UsersController')->only('index');

    Route::prefix('users/me')->group(function() {
        Route::get('', 'UsersController@show');

        // TODO: move the saved news functionality to /ballots/{ballot_id}/news/saved
        Route::resource('news', 'UserNewsController')->only('index', 'update', 'destroy');
        
        Route::resource('ballots', 'UserBallotsController')->except('update');

        Route::resource('ballots/{ballot_id}/candidates', 'UserBallotCandidatesController')->except('store', 'show')->middleware('ballot-valid-user:ballot_id');

        Route::get('ballots/{ballot}/candidates/{candidate}/news', function(Request $request, UserBallot $ballot, ConsolidatedCandidate $candidate) {
            return response()->json($candidate->news->take(10), 200);
        })->middleware('ballot-valid-user:ballot');

        Route::resource('ballots/{ballot}/elections', 'UserBallotElectionsController')->only('index')->middleware('ballot-valid-user:ballot');

        Route::get('ballots/{ballot}/news', function(Request $request, UserBallot $ballot) {
            $ballot_manager = new BallotManager();

            return response()->json($ballot_manager->get_news_from_ballot($ballot), 200);
        })->middleware('ballot-valid-user:ballot');

        Route::get('ballots/{ballot}/tweets', function(Request $request, UserBallot $ballot) {
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