<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\DataSources\Ballotpedia\Ballotpedia_CSV_File_Source;
use App\DataLayer\EloquentModelTransferManager;
use App\DataLayer\Election\ElectionConsolidator;
use App\DataLayer\Candidate\CandidateConsolidator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public $bindings = [
        Ballotpedia_CSV_File_Source::class => Ballotpedia_CSV_File_Source::class,
        EloquentModelTransferManager::class => EloquentModelTransferManager::class,
        ElectionConsolidator::class => ElectionConsolidator::class,
        CandidateConsolidator::class => CandidateConsolidator::class,
    ];

    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
