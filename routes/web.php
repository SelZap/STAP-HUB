<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrafficDataController;

// for traffic data archive
Route::get('/traffic/archive', [TrafficDataController:: class, 'index'])->name('traffic.archive');
Route::get('/traffic/archive/export-csv', [TrafficDataController:: class, 'exportCSV'])->name('traffic.export-csv');
Route::get('/traffic/archive/export-pdf', [TrafficDataController::class, 'exportPDF'])->name('traffic.export-pdf');

// for landing page
Route::get('/', function () {
    return view('landing');
});

Route::get('/homepage', function () {
    return view('landing');
});

// Add other routes as needed
Route::get('/traffic-footage', function () {
    return view('traffic-footage');
})->name('traffic.footage');

Route::get('/traffic-data-archive', function () {
    return view('traffic-data-archive');
})->name('traffic.archive');

Route::get('/vehicle-count', function () {
    return view('vehicle-count');
})->name('vehicle.count');

Route::get('/feedbacks', function () {
    return view('feedbacks');
})->name('feedbacks');