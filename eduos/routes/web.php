<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TextbookController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Local-only design QA: renders the dashboard as user 1 without a session (headless screenshots)
if (app()->environment('local')) {
    Route::get('/design-preview', function () {
        auth()->login(\App\Models\User::find(1));

        return app(DashboardController::class)->index();
    });
}

// Public open-data APIs (FR-NTR-13, FR-NSR-05) — unauthenticated by design
Route::get('/api/catalogue', [\App\Http\Controllers\PublicApiController::class, 'catalogue'])->name('api.catalogue');
Route::get('/api/schools', [\App\Http\Controllers\PublicApiController::class, 'schools'])->name('api.schools');
Route::get('/api/stats', [\App\Http\Controllers\PublicApiController::class, 'stats'])->name('api.stats');
Route::get('/api/openapi.json', [\App\Http\Controllers\PublicApiController::class, 'openapi'])->name('api.openapi');

// AUTH-02/03/04: forgot / reset / MFA challenge (guest routes)
Route::get('/forgot-password', [\App\Http\Controllers\AuthExtrasController::class, 'forgotForm'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\AuthExtrasController::class, 'sendReset'])->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\AuthExtrasController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\AuthExtrasController::class, 'reset'])->name('password.update');
Route::get('/mfa', [\App\Http\Controllers\AuthExtrasController::class, 'mfaChallenge'])->name('mfa.challenge');
Route::post('/mfa', [\App\Http\Controllers\AuthExtrasController::class, 'mfaVerify'])->name('mfa.verify');

Route::get('/login', [PlatformController::class, 'login'])->name('login');
Route::post('/login', [PlatformController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [PlatformController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create', [SchoolController::class, 'create'])->name('schools.create');
    Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
    Route::get('/schools/{school}/students', [SchoolController::class, 'students'])->name('schools.students');
    Route::get('/locale/{locale}', function (string $locale) {
        abort_unless(in_array($locale, ['en', 'fr']), 404);
        session(['locale' => $locale]);

        return back();
    })->name('locale');

    Route::get('/textbooks', [TextbookController::class, 'index'])->name('textbooks.index');
    Route::get('/textbooks/{textbook}', [TextbookController::class, 'show'])->name('textbooks.show');
    Route::post('/textbooks/{textbook}/transition', [TextbookController::class, 'transition'])->name('textbooks.transition')->middleware('can:curriculum');
    Route::post('/textbooks/{textbook}/batches', [TextbookController::class, 'storeBatch'])->name('textbooks.batches.store');

    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
    Route::post('/warehouses/{warehouse}/receive', [WarehouseController::class, 'receive'])->name('warehouses.receive')->middleware('can:warehouse-ops');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store')->middleware('can:warehouse-ops');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{shipment}/dispatch', [ShipmentController::class, 'dispatchShipment'])->name('shipments.dispatch')->middleware('can:warehouse-ops');
    Route::post('/shipments/{shipment}/receive', [ShipmentController::class, 'receive'])->name('shipments.receive');

    Route::post('/textbooks/{textbook}/editions', [TextbookController::class, 'storeEdition'])->name('textbooks.editions.store')->middleware('can:curriculum');
    Route::post('/textbooks/{textbook}/granularity', [TextbookController::class, 'setGranularity'])->name('textbooks.granularity')->middleware('can:curriculum');
    Route::post('/shipments/{shipment}/resolve', [ShipmentController::class, 'resolve'])->name('shipments.resolve')->middleware('can:ministry');

    Route::post('/schools/{school}/assign', [\App\Http\Controllers\SchoolOpsController::class, 'assign'])->name('schoolops.assign')->middleware('can:school-ops');
    Route::post('/assignments/{assignment}/return', [\App\Http\Controllers\SchoolOpsController::class, 'returnBooks'])->name('schoolops.return')->middleware('can:school-ops');
    Route::post('/schools/{school}/enrolment', [\App\Http\Controllers\SchoolOpsController::class, 'submitEnrolment'])->name('schoolops.enrolment');
    Route::post('/enrolments/{enrolment}/validate', [\App\Http\Controllers\SchoolOpsController::class, 'validateEnrolment'])->name('schoolops.enrolment.validate')->middleware('can:division');

    Route::get('/campaigns', [\App\Http\Controllers\SchoolOpsController::class, 'campaigns'])->name('campaigns.index');
    Route::post('/campaigns', [\App\Http\Controllers\SchoolOpsController::class, 'openCampaign'])->name('campaigns.open')->middleware('can:ministry');
    Route::get('/campaigns/{campaign}', [\App\Http\Controllers\SchoolOpsController::class, 'showCampaign'])->name('campaigns.show');
    Route::post('/campaigns/{campaign}/submit', [\App\Http\Controllers\SchoolOpsController::class, 'submitCount'])->name('campaigns.submit');
    Route::post('/campaigns/{campaign}/close', [\App\Http\Controllers\SchoolOpsController::class, 'closeCampaign'])->name('campaigns.close')->middleware('can:ministry');

    Route::get('/redistribution', [\App\Http\Controllers\RedistributionController::class, 'index'])->name('redistribution.index');
    Route::post('/redistribution/generate', [\App\Http\Controllers\RedistributionController::class, 'generate'])->name('redistribution.generate')->middleware('can:ministry');
    Route::post('/redistribution/{proposal}/approve', [\App\Http\Controllers\RedistributionController::class, 'approve'])->name('redistribution.approve')->middleware('can:division');
    Route::post('/redistribution/{proposal}/reject', [\App\Http\Controllers\RedistributionController::class, 'reject'])->name('redistribution.reject');

    Route::get('/about', fn () => view('about.index'))->name('about');

    // PLAN module (screens 21–28)
    Route::get('/plan', [\App\Http\Controllers\PlanController::class, 'index'])->name('plan.index');
    Route::post('/plan', [\App\Http\Controllers\PlanController::class, 'store'])->name('plan.store')->middleware('can:programme');
    Route::get('/plan/{campaign}', [\App\Http\Controllers\PlanController::class, 'show'])->name('plan.show');
    Route::post('/plan/{campaign}/transition', [\App\Http\Controllers\PlanController::class, 'transition'])->name('plan.transition')->middleware('can:programme');
    Route::post('/plan/{campaign}/execute', [\App\Http\Controllers\PlanController::class, 'execute'])->name('plan.execute')->middleware('can:programme');
    Route::post('/allocations/{allocation}', [\App\Http\Controllers\PlanController::class, 'updateLine'])->name('plan.line')->middleware('can:programme');

    // LOG module (screens 58–64)
    Route::get('/logistics', [\App\Http\Controllers\LogisticsController::class, 'index'])->name('logistics.index');
    Route::post('/logistics/vehicles', [\App\Http\Controllers\LogisticsController::class, 'storeVehicle'])->name('logistics.vehicles')->middleware('can:logistics');
    Route::post('/logistics/drivers', [\App\Http\Controllers\LogisticsController::class, 'storeDriver'])->name('logistics.drivers')->middleware('can:logistics');
    Route::post('/trips/{trip}/incident', [\App\Http\Controllers\LogisticsController::class, 'incident'])->name('trips.incident')->middleware('can:logistics');
    Route::post('/trips/{trip}/arrive', [\App\Http\Controllers\LogisticsController::class, 'arrive'])->name('trips.arrive')->middleware('can:logistics');

    // SHIP additions (screens 46–57)
    Route::post('/shipments/{shipment}/approve', [ShipmentController::class, 'approve'])->name('shipments.approve')->middleware('can:warehouse-approve');
    Route::get('/shipments/{shipment}/picking', [ShipmentController::class, 'picking'])->name('shipments.picking');
    Route::get('/shipments/{shipment}/pod', [ShipmentController::class, 'pod'])->name('shipments.pod');
    Route::get('/schedule', [ShipmentController::class, 'schedule'])->name('shipments.schedule');
    Route::get('/network', [ShipmentController::class, 'network'])->name('shipments.network');

    // EXC + ADM-03 + AUTH-05/06 + REP-04
    Route::get('/exceptions', [\App\Http\Controllers\ExceptionController::class, 'index'])->name('exceptions.index');
    Route::post('/exceptions/escalate', [\App\Http\Controllers\ExceptionController::class, 'escalate'])->name('exceptions.escalate');
    Route::get('/audit-trail', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit.index');
    Route::get('/profile/mfa', [\App\Http\Controllers\AuthExtrasController::class, 'mfaSetup'])->name('mfa.setup');
    Route::post('/profile/mfa', [\App\Http\Controllers\AuthExtrasController::class, 'mfaEnable'])->name('mfa.enable');
    Route::post('/profile/mfa/disable', [\App\Http\Controllers\AuthExtrasController::class, 'mfaDisable'])->name('mfa.disable');
    Route::get('/profile/sessions', [\App\Http\Controllers\AuthExtrasController::class, 'sessions'])->name('sessions.index');
    Route::post('/profile/sessions/revoke', [\App\Http\Controllers\AuthExtrasController::class, 'revokeOtherSessions'])->name('sessions.revoke');
    Route::get('/exports', fn () => view('reports.exports'))->name('exports.index');
    Route::get('/reports/shipments.csv', [\App\Http\Controllers\PublicApiController::class, 'shipmentsCsv'])->name('reports.shipments.csv');
    Route::get('/reports/stock.csv', [\App\Http\Controllers\PublicApiController::class, 'stockCsv'])->name('reports.stock.csv');

    // Profile & password management
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\ProfileController::class, 'resetPassword'])->name('users.reset')->middleware('can:ministry');
    Route::post('/users/{user}/update', [PlatformController::class, 'updateUser'])->name('users.update')->middleware('can:ministry');

    // QA, draft edit, enrolment rejection, inspection resolution
    Route::post('/batches/{batch}/qa', [TextbookController::class, 'batchQa'])->name('batches.qa')->middleware('can:procurement');
    Route::post('/textbooks/{textbook}/update', [TextbookController::class, 'update'])->name('textbooks.update')->middleware('can:curriculum');
    Route::post('/enrolments/{enrolment}/reject', [\App\Http\Controllers\SchoolOpsController::class, 'rejectEnrolment'])->name('schoolops.enrolment.reject')->middleware('can:division');
    Route::post('/inspections/{inspection}/resolve', [\App\Http\Controllers\InspectionController::class, 'resolve'])->name('inspections.resolve')->middleware('can:inspect');

    // Warehouse cycle counts and inter-warehouse transfers
    Route::post('/warehouses/{warehouse}/count', [WarehouseController::class, 'count'])->name('warehouses.count')->middleware('can:warehouse-ops');
    Route::post('/warehouses/{warehouse}/transfer', [WarehouseController::class, 'transfer'])->name('warehouses.transfer')->middleware('can:warehouse-approve');

    // School edit + learner detail + supplier edit
    Route::post('/schools/{school}/update', [SchoolController::class, 'update'])->name('schools.update')->middleware('can:division');
    Route::get('/students/{student}', [SchoolController::class, 'student'])->name('students.show');
    Route::post('/suppliers/{supplier}/update', [\App\Http\Controllers\ProcurementController::class, 'updateSupplier'])->name('suppliers.update')->middleware('can:procurement');

    Route::get('/forecast', [\App\Http\Controllers\ForecastController::class, 'index'])->name('forecast.index');
    Route::get('/copies/{copy}', [TextbookController::class, 'copy'])->name('copies.show');
    Route::post('/copies/{copy}/transition', [TextbookController::class, 'copyTransition'])->name('copies.transition');
    Route::post('/textbooks', [TextbookController::class, 'store'])->name('textbooks.store')->middleware('can:curriculum');
    Route::post('/scan', [TextbookController::class, 'scan'])->name('scan');

    Route::middleware('can:procurement')->group(function () {
        Route::get('/procurement', [\App\Http\Controllers\ProcurementController::class, 'index'])->name('procurement.index');
        Route::post('/procurement', [\App\Http\Controllers\ProcurementController::class, 'store'])->name('procurement.store');
        Route::post('/procurement/{order}/delivered', [\App\Http\Controllers\ProcurementController::class, 'markDelivered'])->name('procurement.delivered');
    });
    Route::middleware('can:inspect')->group(function () {
        Route::get('/inspections', [\App\Http\Controllers\InspectionController::class, 'index'])->name('inspections.index');
        Route::post('/inspections', [\App\Http\Controllers\InspectionController::class, 'store'])->name('inspections.store');
    });
    Route::post('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel')->middleware('can:warehouse-approve');
    Route::get('/textbooks/{textbook}/copies', [TextbookController::class, 'copies'])->name('textbooks.copies');
    Route::post('/alerts/read-all', [\App\Http\Controllers\PlatformController::class, 'markAllRead'])->name('alerts.readall');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store')->middleware('can:warehouse-ops');

    Route::post('/users', [PlatformController::class, 'storeUser'])->name('users.store')->middleware('can:ministry');
    Route::post('/users/{user}/toggle', [PlatformController::class, 'toggleUser'])->name('users.toggle')->middleware('can:ministry');
    Route::post('/suppliers', [\App\Http\Controllers\ProcurementController::class, 'storeSupplier'])->name('suppliers.store')->middleware('can:ministry');
    Route::post('/schools/{school}/transition', [SchoolController::class, 'transition'])->name('schools.transition')->middleware('can:ministry');
    Route::post('/schools/{school}/students', [SchoolController::class, 'storeStudent'])->name('schools.students.store')->middleware('can:school-ops');
    Route::get('/reports/coverage.csv', [\App\Http\Controllers\PublicApiController::class, 'coverageCsv'])->name('reports.coverage.csv');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/alerts', [PlatformController::class, 'alerts'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [PlatformController::class, 'markRead'])->name('alerts.read');
    Route::get('/users', [PlatformController::class, 'users'])->name('users.index');
    Route::get('/settings', [PlatformController::class, 'settings'])->name('settings.index');
    Route::post('/settings/verify-chains', function () {
        $code = \Illuminate\Support\Facades\Artisan::call('eduos:verify-chains');
        $out = trim(\Illuminate\Support\Facades\Artisan::output());

        return back()->with($code === 0 ? 'flash' : 'flash_error', $out);
    })->name('settings.verify');
});
