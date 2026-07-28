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
