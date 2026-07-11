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
    Route::post('/textbooks/{textbook}/transition', [TextbookController::class, 'transition'])->name('textbooks.transition')->middleware('can:ministry');
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

    Route::post('/textbooks/{textbook}/editions', [TextbookController::class, 'storeEdition'])->name('textbooks.editions.store')->middleware('can:ministry');
    Route::post('/textbooks/{textbook}/granularity', [TextbookController::class, 'setGranularity'])->name('textbooks.granularity')->middleware('can:ministry');
    Route::post('/shipments/{shipment}/resolve', [ShipmentController::class, 'resolve'])->name('shipments.resolve')->middleware('can:ministry');

    Route::post('/schools/{school}/assign', [\App\Http\Controllers\SchoolOpsController::class, 'assign'])->name('schoolops.assign')->middleware('can:school-ops');
    Route::post('/assignments/{assignment}/return', [\App\Http\Controllers\SchoolOpsController::class, 'returnBooks'])->name('schoolops.return')->middleware('can:school-ops');
    Route::post('/schools/{school}/enrolment', [\App\Http\Controllers\SchoolOpsController::class, 'submitEnrolment'])->name('schoolops.enrolment');
    Route::post('/enrolments/{enrolment}/validate', [\App\Http\Controllers\SchoolOpsController::class, 'validateEnrolment'])->name('schoolops.enrolment.validate');

    Route::get('/campaigns', [\App\Http\Controllers\SchoolOpsController::class, 'campaigns'])->name('campaigns.index');
    Route::post('/campaigns', [\App\Http\Controllers\SchoolOpsController::class, 'openCampaign'])->name('campaigns.open')->middleware('can:ministry');
    Route::get('/campaigns/{campaign}', [\App\Http\Controllers\SchoolOpsController::class, 'showCampaign'])->name('campaigns.show');
    Route::post('/campaigns/{campaign}/submit', [\App\Http\Controllers\SchoolOpsController::class, 'submitCount'])->name('campaigns.submit');
    Route::post('/campaigns/{campaign}/close', [\App\Http\Controllers\SchoolOpsController::class, 'closeCampaign'])->name('campaigns.close')->middleware('can:ministry');

    Route::get('/redistribution', [\App\Http\Controllers\RedistributionController::class, 'index'])->name('redistribution.index');
    Route::post('/redistribution/generate', [\App\Http\Controllers\RedistributionController::class, 'generate'])->name('redistribution.generate')->middleware('can:ministry');
    Route::post('/redistribution/{proposal}/approve', [\App\Http\Controllers\RedistributionController::class, 'approve'])->name('redistribution.approve')->middleware('can:ministry');
    Route::post('/redistribution/{proposal}/reject', [\App\Http\Controllers\RedistributionController::class, 'reject'])->name('redistribution.reject');

    Route::get('/about', fn () => view('about.index'))->name('about');

    Route::get('/forecast', [\App\Http\Controllers\ForecastController::class, 'index'])->name('forecast.index');
    Route::get('/copies/{copy}', [TextbookController::class, 'copy'])->name('copies.show');
    Route::post('/copies/{copy}/transition', [TextbookController::class, 'copyTransition'])->name('copies.transition');
    Route::post('/textbooks', [TextbookController::class, 'store'])->name('textbooks.store')->middleware('can:ministry');
    Route::post('/scan', [TextbookController::class, 'scan'])->name('scan');

    Route::middleware('can:ministry')->group(function () {
        Route::get('/procurement', [\App\Http\Controllers\ProcurementController::class, 'index'])->name('procurement.index');
        Route::post('/procurement', [\App\Http\Controllers\ProcurementController::class, 'store'])->name('procurement.store');
        Route::post('/procurement/{order}/delivered', [\App\Http\Controllers\ProcurementController::class, 'markDelivered'])->name('procurement.delivered');
        Route::get('/inspections', [\App\Http\Controllers\InspectionController::class, 'index'])->name('inspections.index');
        Route::post('/inspections', [\App\Http\Controllers\InspectionController::class, 'store'])->name('inspections.store');
    });
    Route::post('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel')->middleware('can:warehouse-ops');
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
