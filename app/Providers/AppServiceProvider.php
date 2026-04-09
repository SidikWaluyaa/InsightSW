<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        Gate::define('manage-users', function (\App\Models\User $user) {
            return $user->isAdmin();
        });

        Gate::define('access-cs-dashboard', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->isEditor();
        });

        Gate::define('sync-sleekflow', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->isEditor();
        });
    }
}
