<?php

namespace App\Providers;

use App\Helpers\RequestStatusChecker;
use Illuminate\Support\ServiceProvider;
use App\Helpers\StockNumberGenerator;
use App\Helpers\UserRoleChecker;

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
        
        $this->app->singleton(UserRoleChecker::class, function ($app) {
            return new UserRoleChecker();
        });
        
        $this->app->singleton(RequestStatusChecker::class, function ($app) {
            return new RequestStatusChecker();
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
