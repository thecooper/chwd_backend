<?php

namespace App\Providers;

use App\Models\Election\ElectionConsolidator;
use App\Models\EloquentModelTransferManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public $bindings = [
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
