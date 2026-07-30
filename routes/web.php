<?php

use Illuminate\Support\Facades\Route;

// Home Landing Page Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Privacy Policy Route
Route::get('/privacy-police', function () {
    return view('privacy');
})->name('privacy.police');

// Named login route for Filament redirects
Route::get('/login', function () {
    return redirect('/driver/');
})->name('login');

// Unified API Login route managed strictly by the web middleware group
Route::post('/api/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
