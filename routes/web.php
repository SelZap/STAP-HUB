<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// PUBLIC ROUTES (no auth required)
// ─────────────────────────────────────────────

use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\LiveFeedController;
use App\Http\Controllers\Public\TrafficHistoryController;
use App\Http\Controllers\Public\WeatherController;
use App\Http\Controllers\Public\VehicleCountController;
use App\Http\Controllers\Public\FootageRequestController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

// Live camera feed
Route::get('/live', [LiveFeedController::class, 'index'])->name('live-feed');
Route::get('/live/cameras', [LiveFeedController::class, 'cameras'])->name('live-feed.cameras');

// Traffic data archive
Route::get('/traffic', [TrafficHistoryController::class, 'index'])->name('traffic.index');
Route::get('/traffic/list', [TrafficHistoryController::class, 'list'])->name('traffic.list');
Route::get('/traffic/cameras', [TrafficHistoryController::class, 'cameras'])->name('traffic.cameras');

// Rain & weather
Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
Route::get('/weather/latest', [WeatherController::class, 'latest'])->name('weather.latest');
Route::get('/weather/history', [WeatherController::class, 'history'])->name('weather.history');

// Vehicle count
Route::get('/vehicle-count', [VehicleCountController::class, 'index'])->name('vehicle-count.index');
Route::get('/vehicle-count/summary', [VehicleCountController::class, 'summary'])->name('vehicle-count.summary');
Route::get('/vehicle-count/live', [VehicleCountController::class, 'live'])->name('vehicle-count.live');

// Footage request form submission (from modal)
Route::post('/footage-request', [FootageRequestController::class, 'store'])->name('footage-request.store');


// ─────────────────────────────────────────────
// ADMIN AUTH ROUTES (no JWT required)
// ─────────────────────────────────────────────

use App\Http\Controllers\Admin\AuthController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
});


// ─────────────────────────────────────────────
// ADMIN PROTECTED ROUTES (JWT required)
// ─────────────────────────────────────────────

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CameraController;
use App\Http\Controllers\Admin\TrafficLogController;
use App\Http\Controllers\Admin\TrafficLightController;
use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\AccountController;

Route::prefix('admin')->name('admin.')->middleware('auth.admin')->group(function () {

    // System control panel
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::post('/nodes/{nodeId}/mode', [DashboardController::class, 'setNodeMode'])->name('nodes.mode');
    Route::post('/nodes/{nodeId}/restart', [DashboardController::class, 'restartNode'])->name('nodes.restart');

    // Camera feeds & diagnostics
    Route::get('/cameras', [CameraController::class, 'index'])->name('cameras.index');
    Route::get('/cameras/list', [CameraController::class, 'list'])->name('cameras.list');
    Route::get('/cameras/{id}', [CameraController::class, 'show'])->name('cameras.show');
    Route::patch('/cameras/{id}', [CameraController::class, 'update'])->name('cameras.update');
    Route::post('/cameras/{id}/enable', [CameraController::class, 'enable'])->name('cameras.enable');
    Route::post('/cameras/{id}/disable', [CameraController::class, 'disable'])->name('cameras.disable');

    // Traffic logs
    Route::get('/traffic-logs', [TrafficLogController::class, 'index'])->name('traffic-logs.index');
    Route::get('/traffic-logs/list', [TrafficLogController::class, 'list'])->name('traffic-logs.list');
    Route::get('/traffic-logs/{id}', [TrafficLogController::class, 'show'])->name('traffic-logs.show');

    // Traffic light control
    Route::get('/traffic-lights', [TrafficLightController::class, 'index'])->name('traffic-lights.index');
    Route::get('/traffic-lights/list', [TrafficLightController::class, 'list'])->name('traffic-lights.list');
    Route::get('/traffic-lights/{id}', [TrafficLightController::class, 'show'])->name('traffic-lights.show');
    Route::post('/traffic-lights/{id}/state', [TrafficLightController::class, 'setState'])->name('traffic-lights.state');

    // Alerts & system status
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/list', [AlertController::class, 'list'])->name('alerts.list');
    Route::get('/alerts/{id}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{id}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

    // Footage requests
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/list', [RequestController::class, 'list'])->name('requests.list');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/reviewed', [RequestController::class, 'markReviewed'])->name('requests.reviewed');
    Route::post('/requests/{id}/requirements', [RequestController::class, 'sendRequirements'])->name('requests.requirements');
    Route::post('/requests/{id}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{id}/reject', [RequestController::class, 'reject'])->name('requests.reject');

    // Admin account management
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::patch('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy');
});