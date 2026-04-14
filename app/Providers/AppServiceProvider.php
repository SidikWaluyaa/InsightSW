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

        // RBAC Gates
        Gate::define('manage-users', function (\App\Models\User $user) {
            return $user->isAdmin();
        });

        Gate::define('access-marketing', function (\App\Models\User $user) {
            return in_array($user->role, ['Admin', 'Editor', 'Viewer']);
        });

        Gate::define('access-cs', function (\App\Models\User $user) {
            return in_array($user->role, ['Admin', 'Editor', 'CS', 'Viewer']);
        });

        Gate::define('access-cx', function (\App\Models\User $user) {
            return in_array($user->role, ['Admin', 'Editor', 'CX', 'Viewer']);
        });

        Gate::define('access-finance', function (\App\Models\User $user) {
            return in_array($user->role, ['Admin', 'Editor', 'Finance', 'Viewer']);
        });

        Gate::define('access-gudang', function (\App\Models\User $user) {
            return in_array($user->role, ['Admin', 'Editor', 'Gudang', 'Viewer']);
        });

        Gate::define('sync-sleekflow', function (\App\Models\User $user) {
            return in_array($user->role, ['Admin', 'Editor', 'CS']);
        });
    }
}
