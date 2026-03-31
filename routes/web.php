<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\LandingController;

Route::get('/', [LandingController::class, 'index'])->name('landing');