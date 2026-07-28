<?php

use Illuminate\Support\Facades\Route;

// Redirect root to /privacy-police
Route::get('/', function () {
    return redirect('/privacy-police');
});

// Privacy Policy Route
Route::get('/privacy-police', function () {
    return view('welcome');
})->name('privacy.police');
