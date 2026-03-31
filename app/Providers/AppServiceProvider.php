<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);

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
