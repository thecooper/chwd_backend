<?php

namespace App\Providers;

use App\DataSources\Ballotpedia_CSV_File_Source;
use App\Models\EloquentModelTransferManager;
use App\Models\Election\ElectionConsolidator;
use Illuminate\Support\ServiceProvider;

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
    ];

    public function boot()
    {
        //
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
