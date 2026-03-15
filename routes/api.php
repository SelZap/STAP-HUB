<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// STAP NODE API ROUTES
// All routes protected by AuthenticateStapNode middleware.
// Nodes must send:  Authorization: Bearer <api_key>
// ─────────────────────────────────────────────

use App\Http\Controllers\Api\TrafficSnapshotController;
use App\Http\Controllers\Api\WeatherLogController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\NodeHeartbeatController;

Route::middleware('auth.node')->prefix('v1')->name('api.v1.')->group(function () {

    // Node heartbeat / status ping
    Route::post('/heartbeat', [NodeHeartbeatController::class, 'ping'])->name('heartbeat');

    // Traffic snapshot ingestion
    Route::post('/snapshots', [TrafficSnapshotController::class, 'store'])->name('snapshots.store');

    // Weather log ingestion
    Route::post('/weather', [WeatherLogController::class, 'store'])->name('weather.store');

    // Alert ingestion
    Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
});