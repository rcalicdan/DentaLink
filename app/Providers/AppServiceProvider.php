<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Model::automaticallyEagerLoadRelationships();
        Gate::define('view-dashboard', function ($user) {
            return $user->isAdmin() || $user->isSuperadmin();
        });
        Gate::define('view-ai-assistant', function ($user) {
            return $user->isSuperadmin();
        });
    }
}
