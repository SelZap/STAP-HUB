<?php

use Illuminate\Support\Facades\Route;

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