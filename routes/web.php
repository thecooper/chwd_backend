<?php

use App\DataSources\Ballotpedia_CSV_File_Source;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});

Route::get('/import', function () {
    $source_processor = new Ballotpedia_CSV_File_Source();
    if ($source_processor->CanProcess()) {
        $file_count = $source_processor->Process();
    }

    return "Processed {$file_count} files";
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
