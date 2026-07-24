<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Movement;
use App\Observers\MovementObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Movement::observe(MovementObserver::class);
    }
}
