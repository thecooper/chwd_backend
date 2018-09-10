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

use App\UserBallot;
use Illuminate\Http\Request;
use App\DataSources\Ballotpedia_CSV_File_Source;
use App\Models\Candidate\ConsolidatedCandidate;
use App\News;
use App\DataSources\NewsAPIDataSource;
use App\Models\BallotManager;

// Route::get('import', 'ImportController@show'); // Importing now done through cli: 'php artisan import'

Route::middleware('auth.basic')->group(function () {
    Route::resource('users', 'UsersController')->only('index');

    Route::prefix('users/me')->group(function() {
        Route::get('', 'UsersController@show');

        Route::resource('news', 'UserNewsController')->only('index', 'update', 'destroy');
        Route::resource('ballots', 'UserBallotsController')->except('update');
        Route::resource('ballots/{ballot_id}/candidates', 'UserBallotCandidatesController')
            ->except('store', 'show')
            ->middleware('ballot-valid-user:ballot_id');

        Route::resource('ballots/{ballot}/elections', 'UserBallotElectionsController')
            ->only('index')
            ->middleware('ballot-valid-user:ballot');

        Route::get('ballots/{ballot}/candidates/news', function(Request $request, UserBallot $ballot) {
            $ballot_manager = new BallotManager();

            return response()->json($ballot_manager->get_news_from_ballot($ballot), 200);
        })->middleware('ballot-valid-user:ballot');

        Route::get('ballots/{ballot}/candidates/tweets', function(Request $request, UserBallot $ballot) {
            return response()->json([], 200);
        })->middleware('ballot-valid-user:ballot');
    });


    Route::resource('candidates', 'CandidatesController')->only('index', 'show');
    
    Route::get('candidates/{id}/news', function(Request $request, NewsAPIDataSource $news_data_source, $id) {
        $candidate = ConsolidatedCandidate::find($id);

        if($candidate == null) { return response()->json('candidate not found', 404); }

        $query = "\"{$candidate->name}\" {$candidate->election->state_abbreviation}";

        $articles = $news_data_source->get_articles($query);

        foreach($articles as $article) {
            $existing_article = News::where('url', $article->url)->first();

            // var_dump($existing_article->url);

            if($existing_article == null) {
                // print_r("Saving an article!!!");
                $existing_article = new News();
            }

            $existing_article->url = $article->url;
            $existing_article->thumbnail_url = $article->thumbnail_url ?? '';
            $existing_article->title = $article->title;
            $existing_article->description = $article->description ?? '';
            $existing_article->candidate_id = $id;
            $existing_article->publish_date = (new DateTime($article->publish_date))->format('Y-m-d H:i:s');
            $existing_article->save();
        }

        return response()->json($articles, 200);
    });

    Route::resource('elections', 'ElectionsController')->only('index', 'show');
    Route::get('elections/{id}/races', 'ElectionsController@races');
    Route::get('elections/{id}/candidates', 'ElectionsController@election_candidates');

});

Route::post('users', 'UsersController@store');

// Route::post('users', 'UsersController@create');
