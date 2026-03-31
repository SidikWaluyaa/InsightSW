<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\Gate::define('manage-users', function (\App\Models\User $user) {
            return $user->isAdmin();
        });

        \Illuminate\Support\Facades\Gate::define('access-cs-dashboard', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->isEditor();
        });

        \Illuminate\Support\Facades\Gate::define('sync-sleekflow', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->isEditor();
        });
    }
}
