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

Route::middleware('auth.basic')->group(function () {
    Route::get('users', 'UsersController@index');
    Route::get('users/me', 'UsersController@index');

    Route::get('users/me', 'UsersController@show');

    Route::get('candidates', 'CandidatesController@index');

    Route::resource('elections', 'ElectionsController')->only('index', 'store', 'show');
});

Route::post('users', 'UsersController@create');
