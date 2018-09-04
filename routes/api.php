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
use App\DataSources\NewsAPIDataSource;
 
// Route::get('import', 'ImportController@show'); // Importing now done through cli: 'php artisan import'

Route::middleware('auth.basic')->group(function () {
    Route::resource('users', 'UsersController')->only('index');

    Route::get('users/me', 'UsersController@index');
    Route::get('users/me', 'UsersController@show');

    // Route::get('users/me/ballots', function() {
    //     $user = Auth::user();
    //     $ballot_list = array();

    //     foreach($user->ballots as $ballot) {
    //         $ballot_array = array(
    //             "id" => $ballot["id"],
    //             "address_line_1" => $ballot["address_line_1"],
    //             "address_line_2" => $ballot["address_line_2"],
    //             "city" => $ballot["city"],
    //             "state_abbreviation" => $ballot["state_abbreviation"],
    //             "zip" => $ballot["zip"],
    //         );

    //         array_push($ballot_list, $ballot_array);
    //     }

    //     return response()->json($ballot_list, 200);
    // });

    Route::resource('candidates', 'CandidatesController')->only('index', 'show');
    
    Route::get('candidates/{id}/media/news', function(Request $request, NewsAPIDataSource $news_data_source, $id) {
        $candidate = ConsolidatedCandidate::find($id);

        if($candidate == null) { return response()->json(null, 404); }

        $query = "\"{$candidate->name}\" {$candidate->election->state_abbreviation}";

        $articles = $news_data_source->get_articles($query);

        return response()->json($articles, 200);
    });

    Route::resource('elections', 'ElectionsController')->only('index', 'show');
    Route::get('elections/{id}/races', 'ElectionsController@races');
    Route::get('elections/{id}/candidates', 'ElectionsController@election_candidates');

});

Route::post('users', 'UsersController@store');

// Route::post('users', 'UsersController@create');
