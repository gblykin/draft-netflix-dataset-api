<?php

namespace App\Providers;

use App\Services\MovieService;
use App\Services\UserService;
use App\Services\ReviewService;
use App\Services\ImportProgressReporter;
use App\Services\ImportLogger;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons for better performance
        $this->app->singleton(MovieService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(ReviewService::class);
        $this->app->singleton(ImportProgressReporter::class);
        $this->app->singleton(ImportLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

