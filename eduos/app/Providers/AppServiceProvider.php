<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** The full FRS role set (docs 04 §6.5, 07 §6, 08 §5). */
    public const ROLES = [
        'ADMIN', 'PROGRAMME_ADMIN', 'CURRICULUM_OFFICER', 'PROCUREMENT_OFFICER', 'TRANSPORT_OFFICER',
        'WAREHOUSE_MANAGER', 'STOREKEEPER', 'WAREHOUSE_OFFICER',   // WAREHOUSE_OFFICER kept as legacy alias of STOREKEEPER
        'DIVISION_OFFICER', 'SUBDIV_OFFICER',
        'SCHOOL_HEAD', 'TEACHER', 'INSPECTOR', 'AUDITOR', 'READONLY',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        $is = fn ($user, array $roles) => in_array($user->role, $roles);

        // National administration
        Gate::define('ministry', fn ($u) => $u->role === 'ADMIN');

        // National programme tier: campaigns, allocation approval (PLAN module)
        Gate::define('programme', fn ($u) => $is($u, ['ADMIN', 'PROGRAMME_ADMIN']));

        // Logistics: vehicles, drivers, trips, incidents (LOG module)
        Gate::define('logistics', fn ($u) => $is($u, ['ADMIN', 'TRANSPORT_OFFICER', 'WAREHOUSE_MANAGER']));

        // Curriculum: the only path to title approval/retirement (FR-NTR-02)
        Gate::define('curriculum', fn ($u) => $is($u, ['ADMIN', 'CURRICULUM_OFFICER']));

        // Procurement: orders, suppliers, batch registration (FRS 04 §6.2)
        Gate::define('procurement', fn ($u) => $is($u, ['ADMIN', 'PROCUREMENT_OFFICER']));

        // Warehouse custody operations: receipts, dispatch, shipments (FRS 08 §5)
        Gate::define('warehouse-ops', fn ($u) => $is($u, ['ADMIN', 'WAREHOUSE_MANAGER', 'STOREKEEPER', 'WAREHOUSE_OFFICER']));

        // Warehouse approvals beyond posting: cancellation (manager tier)
        Gate::define('warehouse-approve', fn ($u) => $is($u, ['ADMIN', 'WAREHOUSE_MANAGER']));

        // Division tier: enrolment validation, redistribution approval, reconciliation
        Gate::define('division', fn ($u) => $is($u, ['ADMIN', 'DIVISION_OFFICER']));

        // Field verification / registration proposals
        Gate::define('subdivision', fn ($u) => $is($u, ['ADMIN', 'DIVISION_OFFICER', 'SUBDIV_OFFICER']));

        // School operations: head teachers and teachers (teachers: own class, school-scoped)
        Gate::define('school-ops', fn ($u) => $is($u, ['ADMIN', 'SCHOOL_HEAD', 'TEACHER']));

        // Inspections and spot checks
        Gate::define('inspect', fn ($u) => $is($u, ['ADMIN', 'INSPECTOR']));

        // Row-level: operate on a specific school
        Gate::define('operate-school', function ($u, $school) {
            return $u->role === 'ADMIN'
                || (in_array($u->role, ['SCHOOL_HEAD', 'TEACHER']) && $u->school_id === $school->id);
        });

        // Read-side: the audit trail (incl. auth events) is for oversight tiers only
        Gate::define('view-audit', fn ($u) => $is($u, ['ADMIN', 'AUDITOR', 'INSPECTOR']));

        // Read-side: learner records are minor-related PII — oversight roles, or your own school
        Gate::define('view-learners', function ($u, $school) {
            return in_array($u->role, ['ADMIN', 'PROGRAMME_ADMIN', 'DIVISION_OFFICER', 'SUBDIV_OFFICER', 'INSPECTOR', 'AUDITOR'])
                || (in_array($u->role, ['SCHOOL_HEAD', 'TEACHER']) && $u->school_id === $school->id);
        });
    }
}
