<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\StockNumberGenerator;

class StockNumberServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StockNumberGenerator::class, function ($app) {
            return new StockNumberGenerator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
