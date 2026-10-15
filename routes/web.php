<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BulkImportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManifestController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchLocatorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RateCalculatorController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Public Portal Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Tracking
Route::get('/track/{awb?}', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/track', [TrackingController::class, 'index'])->name('tracking.search');

// Shipping Rates & Tariff Calculator
Route::get('/rates', [RateCalculatorController::class, 'index'])->name('rates.index');
Route::post('/rates/calculate', [RateCalculatorController::class, 'calculate'])->name('rates.calculate');

// Branch & Drop Point Locator
Route::get('/branches', [BranchLocatorController::class, 'index'])->name('branches.index');

// Authentication & Demo Role Switcher
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/quick', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// AJAX Tariff Calculator
Route::get('/api/calculate-tariff', [ShipmentController::class, 'calculateAjax'])->name('api.calculate_tariff');

// Back-Office Admin & Staff Portal
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments & Waybills
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::get('/shipments/{id}/print', [ShipmentController::class, 'printLabel'])->name('shipments.print');
    Route::post('/shipments/{id}/update-status', [ShipmentController::class, 'updateStatus'])->name('shipments.update_status');
    Route::post('/shipments/{id}/pod', [ShipmentController::class, 'recordPod'])->name('shipments.pod');
    Route::post('/shipments/{id}/assign-courier', [ShipmentController::class, 'assignCourier'])->name('shipments.assign_courier');

    // Bulk Import Center
    Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
    Route::post('/bulk-import/shipments', [BulkImportController::class, 'importShipments'])->name('bulk-import.shipments');
    Route::post('/bulk-import/status', [BulkImportController::class, 'importStatus'])->name('bulk-import.status');
    Route::post('/bulk-import/tariffs', [BulkImportController::class, 'importTariffs'])->name('bulk-import.tariffs');
    Route::get('/bulk-import/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');

    // Manifests & Run-Sheets
    Route::get('/manifests', [ManifestController::class, 'index'])->name('manifests.index');
    Route::get('/manifests/create', [ManifestController::class, 'create'])->name('manifests.create');
    Route::post('/manifests', [ManifestController::class, 'store'])->name('manifests.store');
    Route::get('/manifests/{id}', [ManifestController::class, 'show'])->name('manifests.show');
    Route::get('/manifests/{id}/print', [ManifestController::class, 'printManifest'])->name('manifests.print');
    Route::post('/manifests/{id}/status', [ManifestController::class, 'updateStatus'])->name('manifests.update_status');

    // Tariffs
    Route::get('/tariffs', [TariffController::class, 'index'])->name('tariffs.index');
    Route::post('/tariffs', [TariffController::class, 'store'])->name('tariffs.store');
    Route::delete('/tariffs/{id}', [TariffController::class, 'destroy'])->name('tariffs.destroy');

    // Branches & Network
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{id}', [BranchController::class, 'update'])->name('branches.update');

    // Users & Couriers
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    // Reports & CSV Export
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export');
});
