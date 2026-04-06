<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncidentReportController;

// Public Controllers
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\LiveFeedController;
use App\Http\Controllers\Public\FootageRequestController;
use App\Http\Controllers\Public\TrafficHistoryController;
use App\Http\Controllers\Public\WeatherController;
use App\Http\Controllers\Public\VehicleCountController;

// Admin Controllers
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CameraController;
use App\Http\Controllers\Admin\TrafficLogController;
use App\Http\Controllers\Admin\TrafficLightController;
use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\AccountController;

// ============================================================
// PUBLIC ROUTES (no auth)
// ============================================================

Route::get('/', [LandingController::class, 'index'])
    ->name('public.dashboard');

Route::get('/live', [LiveFeedController::class, 'index'])
    ->name('public.live');

Route::get('/data-request', [FootageRequestController::class, 'index'])
    ->name('public.request');

Route::post('/data-request', [FootageRequestController::class, 'store'])
    ->name('public.request.store');

// Incident Report
Route::get('/incident-report', [IncidentReportController::class, 'create'])->name('incident.create');
Route::post('/incident-report', [IncidentReportController::class, 'store'])->name('incident.store');

// ============================================================
// ADMIN AUTH (no middleware — login/logout)
// ============================================================

Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.post');

Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->name('admin.logout');

// ============================================================
// ADMIN PANEL (JWT protected)
// ============================================================

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth.admin')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Cameras & diagnostics
        Route::get('/cameras', [CameraController::class, 'index'])
            ->name('cameras');

        // Traffic logs
        Route::get('/traffic-logs', [TrafficLogController::class, 'index'])
            ->name('traffic-logs');

        // Traffic light control
        Route::get('/traffic-lights', [TrafficLightController::class, 'index'])
            ->name('traffic-lights');
        Route::post('/traffic-lights/{light_id}/state', [TrafficLightController::class, 'updateState'])
            ->name('traffic-lights.state');
        Route::post('/traffic-lights/{light_id}/mode', [TrafficLightController::class, 'updateMode'])
            ->name('traffic-lights.mode');

        // Alerts
        Route::get('/alerts', [AlertController::class, 'index'])
            ->name('alerts');
        Route::post('/alerts/{alert_id}/resolve', [AlertController::class, 'resolve'])
            ->name('alerts.resolve');

        // Footage requests
        Route::get('/requests', [RequestController::class, 'index'])
            ->name('requests');
        Route::get('/requests/{request_id}', [RequestController::class, 'show'])
            ->name('requests.show');
        Route::post('/requests/{request_id}/message', [RequestController::class, 'sendMessage'])
            ->name('requests.message');
        Route::post('/requests/{request_id}/status', [RequestController::class, 'updateStatus'])
            ->name('requests.status');

        // Account management (superuser only)
        Route::get('/accounts', [AccountController::class, 'index'])
            ->name('accounts');
        Route::post('/accounts', [AccountController::class, 'store'])
            ->name('accounts.store');
        Route::put('/accounts/{admin_id}', [AccountController::class, 'update'])
            ->name('accounts.update');
        Route::delete('/accounts/{admin_id}', [AccountController::class, 'destroy'])
            ->name('accounts.destroy');
        
        // Incident Reports
        Route::get('/admin/incident-reports', [IncidentReportController::class, 'index'])->name('admin.incident-reports.index');
        Route::patch('/admin/incident-reports/{id}/review', [IncidentReportController::class, 'markReviewed'])->name('admin.incident-reports.review');
        Route::get('/admin/incident-reports/pending-count', [IncidentReportController::class, 'pendingCount'])->name('admin.incident-reports.pending-count');

    });