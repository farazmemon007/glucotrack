<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Auto-login default user so dashboard opens directly on first visit
    if (!session('manual_logout')) {
        try {
            $defaultUser = \App\Models\User::first();
            if ($defaultUser) {
                auth()->login($defaultUser, true);
                return redirect()->route('dashboard');
            }
        } catch (\Throwable $e) {
            // Safe fallback if database tables are not yet migrated
        }
    }

    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Forgot / Reset Password Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.send');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::post('/forgot-password/resend', [AuthController::class, 'resendResetCode'])->name('password.resend');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Caregiver Home Dashboard
    Route::get('/dashboard', [DashboardController::class, 'home'])->name('dashboard');

    // Patient's Own Dashboard
    Route::get('/patient/dashboard', [DashboardController::class, 'patientDashboard'])->name('patient.dashboard');

    // Patient Switching
    Route::post('/patients/{patient}/switch', [PatientController::class, 'switchPatient'])->name('patients.switch');

    // Patients Management
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');

    // Daily Logs Management
    Route::get('/patients/{patient}/logs/create', [DailyLogController::class, 'create'])->name('patients.logs.create');
    Route::post('/patients/{patient}/logs', [DailyLogController::class, 'store'])->name('patients.logs.store');
    Route::get('/patients/{patient}/logs/{log}/edit', [DailyLogController::class, 'edit'])->name('patients.logs.edit');
    Route::put('/patients/{patient}/logs/{log}', [DailyLogController::class, 'update'])->name('patients.logs.update');
    Route::delete('/patients/{patient}/logs/{log}', [DailyLogController::class, 'destroy'])->name('patients.logs.destroy');

    // Views & Reports
    Route::get('/patients/{patient}/history', [DashboardController::class, 'history'])->name('patients.history');
    Route::get('/patients/{patient}/sheet', [DashboardController::class, 'sheet'])->name('patients.sheet');
    Route::get('/patients/{patient}/export.pdf', [DashboardController::class, 'exportPdf'])->name('patients.export.pdf');
});
