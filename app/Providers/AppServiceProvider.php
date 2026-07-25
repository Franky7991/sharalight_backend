<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Movement;
use App\Observers\MovementObserver;
use App\Models\CustomerOrderHasProductDetail;
use App\Observers\CustomerOrderHasProductDetailObserver;

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
        CustomerOrderHasProductDetail::observe(CustomerOrderHasProductDetailObserver::class);
    }
}
