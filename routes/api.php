<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverRouteController;
use App\Http\Controllers\Api\DriverStopController;

// Public Auth Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Driver App Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Driver Route Management
    Route::get('/driver/route', [DriverRouteController::class, 'index']);
    Route::get('/driver/profile', [DriverRouteController::class, 'profile']);
    Route::post('/driver/route/start', [DriverRouteController::class, 'start']);
    Route::post('/driver/route/complete', [DriverRouteController::class, 'complete']);

    // Driver Stop Management
    Route::get('/driver/stops/{scheduledStop}', [DriverStopController::class, 'show']);
    Route::post('/driver/stops/{scheduledStop}/pickup', [DriverStopController::class, 'logPickup']);
});
