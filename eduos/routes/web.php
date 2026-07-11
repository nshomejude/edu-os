<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TextbookController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [PlatformController::class, 'login'])->name('login');
Route::post('/login', [PlatformController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [PlatformController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create', [SchoolController::class, 'create'])->name('schools.create');
    Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');

    Route::get('/textbooks', [TextbookController::class, 'index'])->name('textbooks.index');
    Route::get('/textbooks/{textbook}', [TextbookController::class, 'show'])->name('textbooks.show');
    Route::post('/textbooks/{textbook}/transition', [TextbookController::class, 'transition'])->name('textbooks.transition');
    Route::post('/textbooks/{textbook}/batches', [TextbookController::class, 'storeBatch'])->name('textbooks.batches.store');

    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
    Route::post('/warehouses/{warehouse}/receive', [WarehouseController::class, 'receive'])->name('warehouses.receive');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{shipment}/dispatch', [ShipmentController::class, 'dispatchShipment'])->name('shipments.dispatch');
    Route::post('/shipments/{shipment}/receive', [ShipmentController::class, 'receive'])->name('shipments.receive');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/alerts', [PlatformController::class, 'alerts'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [PlatformController::class, 'markRead'])->name('alerts.read');
    Route::get('/users', [PlatformController::class, 'users'])->name('users.index');
    Route::get('/settings', [PlatformController::class, 'settings'])->name('settings.index');
});
