<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyLogApiController;
use App\Http\Controllers\Api\PatientApiController;
use Illuminate\Support\Facades\Route;

// Public Authentication Endpoints
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Endpoints (Requires Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    // Current User
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Patients Management
    Route::get('/patients', [PatientApiController::class, 'index']);
    Route::post('/patients', [PatientApiController::class, 'store']);
    Route::get('/patients/{patient}', [PatientApiController::class, 'show']);
    Route::put('/patients/{patient}', [PatientApiController::class, 'update']);

    // Daily Logs Management
    Route::get('/patients/{patient}/logs', [DailyLogApiController::class, 'index']);
    Route::get('/patients/{patient}/logs/date', [DailyLogApiController::class, 'forDate']);
    Route::post('/patients/{patient}/logs', [DailyLogApiController::class, 'store']);
    Route::get('/patients/{patient}/logs/{log}', [DailyLogApiController::class, 'show']);
    Route::delete('/patients/{patient}/logs/{log}', [DailyLogApiController::class, 'destroy']);

    // Statistics & Reports
    Route::get('/patients/{patient}/stats', [DailyLogApiController::class, 'stats']);
});
