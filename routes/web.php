<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrafficArchiveController;

Route::get('/', function () {
    return view('landing');
});

Route::get('/homepage', function () {
    return view('landing');
});

Route::get('/traffic-footage', function () {
       return view('traffic-footage');
   })->name('traffic.footage');

// Traffic Data Archive Routes - REMOVE THE DUPLICATE
Route::get('/traffic-data-archive', [TrafficArchiveController::class, 'index'])->name('traffic.archive');
Route::get('/api/traffic-archives', [TrafficArchiveController::class, 'getData']);
Route::get('/api/traffic-archives/{id}/download', [TrafficArchiveController::class, 'download']);

Route::get('/vehicle-count', function () {
    return view('vehicle-count');
})->name('vehicle.count');

Route::get('/feedbacks', function () {
    return view('feedbacks');
})->name('feedbacks');