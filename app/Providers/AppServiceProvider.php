<?php

namespace App\Providers;

use App\Http\Controllers\RolController;
use App\Services\PermisoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PermisoService::class, function ($app) {
            return new PermisoService($app->make(RolController::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
