<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Role gates per the FRS permission matrices (simplified three-role demo):
     * ADMIN = ministry-level (everything); WAREHOUSE_OFFICER = custody & catalogue
     * operations; SCHOOL_HEAD = own-school operations only.
     */
    public function boot(): void
    {
        Gate::define('ministry', fn ($user) => $user->role === 'ADMIN');

        Gate::define('warehouse-ops', fn ($user) => in_array($user->role, ['ADMIN', 'WAREHOUSE_OFFICER']));

        Gate::define('school-ops', fn ($user) => in_array($user->role, ['ADMIN', 'SCHOOL_HEAD']));

        // Row-level scoping (FR-NSR/NTR-18): a school head may only operate on their own school
        Gate::define('operate-school', function ($user, $school) {
            return $user->role === 'ADMIN'
                || ($user->role === 'SCHOOL_HEAD' && $user->school_id === $school->id);
        });
    }
}
