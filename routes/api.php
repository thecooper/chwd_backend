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
use App\DataSources\Ballotpedia_CSV_File_Source;
 
Route::middleware('auth.basic')->group(function () {
    Route::resource('users', 'UsersController')->only('index');

    Route::get('users/me', 'UsersController@index');
    Route::get('users/me', 'UsersController@show');

    Route::get('users/me/ballots', function() {
        $user = Auth::user();
        $ballot_list = array();

        foreach($user->ballots as $ballot) {
            $ballot_array = array(
                "id" => $ballot["id"],
                "address_line_1" => $ballot["address_line_1"],
                "address_line_2" => $ballot["address_line_2"],
                "city" => $ballot["city"],
                "state_abbreviation" => $ballot["state_abbreviation"],
                "zip" => $ballot["zip"],
            );

            array_push($ballot_list, $ballot_array);
        }

        return response()->json($ballot_list, 200);
    });

    Route::get('candidates', 'CandidatesController@index');

    Route::resource('elections', 'ElectionsController')->only('index', 'store', 'show');
});

Route::get('import', function (Ballotpedia_CSV_File_Source $source_processor) {
    if ($source_processor->CanProcess()) {
        $file_count = $source_processor->Process();
    }

    return "Processed {$file_count} files";
});

Route::post('users', 'UsersController@store');

// Route::post('users', 'UsersController@create');
