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
use App\Http\Controllers\Admin\AnnouncementController;

// ============================================================
// PUBLIC ROUTES (no auth)
// ============================================================

Route::get('/', [LandingController::class, 'index'])->name('public.dashboard');

Route::get('/live', [LiveFeedController::class, 'index'])->name('public.live');
Route::get('/live/cameras', [LiveFeedController::class, 'cameras'])->name('public.live.cameras');

Route::get('/data-request', [FootageRequestController::class, 'index'])->name('public.request');
Route::post('/data-request', [FootageRequestController::class, 'store'])->name('public.request.store');
Route::get('/data-request/cameras', [FootageRequestController::class, 'cameras'])->name('public.cameras');

// Incident Report
Route::get('/incident-report', [IncidentReportController::class, 'create'])->name('incident.create');
Route::post('/incident-report', [IncidentReportController::class, 'store'])->name('incident.store');
Route::post('/incident-report/validate-email', [IncidentReportController::class, 'validateEmail'])->name('incident.validate-email');

// Public announcements banner
Route::get('/api/announcements/active', [AnnouncementController::class, 'active'])->name('announcements.active');

// ============================================================
// ADMIN AUTH
// ============================================================

Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// ============================================================
// ADMIN PANEL (JWT protected)
// ============================================================

Route::prefix('admin')->name('admin.')->middleware('auth.admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/cameras', [CameraController::class, 'index'])->name('cameras');

    Route::get('/traffic-logs', [TrafficLogController::class, 'index'])->name('traffic-logs');

    Route::get('/traffic-lights', [TrafficLightController::class, 'index'])->name('traffic-lights');
    Route::post('/traffic-lights/{light_id}/state', [TrafficLightController::class, 'updateState'])->name('traffic-lights.state');
    Route::post('/traffic-lights/{light_id}/mode', [TrafficLightController::class, 'updateMode'])->name('traffic-lights.mode');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts');
    Route::post('/alerts/{alert_id}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

    // Footage Requests
    Route::get('/requests', [RequestController::class, 'index'])->name('requests');
    Route::post('/requests/{request_id}/status', [RequestController::class, 'updateStatus'])->name('requests.status');
    Route::post('/requests/{request_id}/email', [RequestController::class, 'sendEmail'])->name('requests.email');

    // Account Management (superuser only)
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{admin_id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{admin_id}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    // Incident Reports
    Route::get('/incident-reports', [IncidentReportController::class, 'index'])->name('incident-reports.index');
    Route::patch('/incident-reports/{id}/review', [IncidentReportController::class, 'markReviewed'])->name('incident-reports.review');
    Route::post('/incident-reports/{id}/email', [IncidentReportController::class, 'sendEmail'])->name('incident-reports.email');
    Route::get('/incident-reports/pending-count', [IncidentReportController::class, 'pendingCount'])->name('incident-reports.pending-count');

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::patch('/announcements/{id}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // AJAX API Endpoints
    Route::get('/api/dashboard/summary',     [DashboardController::class,   'summary']);
    Route::get('/api/cameras',               [CameraController::class,       'list']);
    Route::get('/api/alerts',                [AlertController::class,        'list']);
    Route::get('/api/traffic-logs',          [TrafficLogController::class,   'list']);
    Route::get('/api/traffic-lights',        [TrafficLightController::class, 'list']);
    Route::get('/api/requests',              [RequestController::class,      'list']);
    Route::get('/api/requests/{request_id}', [RequestController::class,      'show']);
    Route::get('/api/announcements',         [AnnouncementController::class, 'list']);
    Route::post('/requests/{id}/requirements', [RequestController::class, 'sendRequirements']);
});