<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes with rate limiting (60 requests per minute)
Route::middleware('throttle:60,1')->prefix('auth')->group(function () {
    // Registration
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');

    // Login
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    // Forgot password
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password');

    // Reset password
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('auth.reset-password');

    // Email verification
    Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('auth.verify-email');

    // Resend verification email (for unauthenticated users)
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('auth:sanctum')
        ->name('auth.resend-verification');
});

// Protected routes (authenticated users only)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    // Get current user
    Route::get('/me', [AuthController::class, 'me'])
        ->name('auth.me');

    // Logout (current device)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    // Logout from all devices
    Route::post('/logout-all', [AuthController::class, 'logoutAll'])
        ->name('auth.logout-all');

    // Resend verification email
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail'])
        ->name('auth.resend-verification-authenticated');
});

// Profile Management Routes (protected)
Route::middleware('auth:sanctum')->prefix('profiles')->group(function () {
    // List all profiles for authenticated user
    Route::get('/', [ProfileController::class, 'index'])
        ->name('profiles.index');

    // Create a new profile
    Route::post('/', [ProfileController::class, 'store'])
        ->name('profiles.store');

    // Get current active profile
    Route::get('/current', [ProfileController::class, 'current'])
        ->name('profiles.current');

    // Profile-specific routes (use {profile} for model binding)
    Route::prefix('{profile}')->group(function () {
        // Get a specific profile
        Route::get('/', [ProfileController::class, 'show'])
            ->name('profiles.show');

        // Update a profile
        Route::put('/', [ProfileController::class, 'update'])
            ->name('profiles.update');

        // Delete a profile
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('profiles.destroy');

        // Switch to a profile
        Route::post('/switch', [ProfileController::class, 'switch'])
            ->name('profiles.switch');

        // Update parental controls
        Route::post('/parental-controls', [ProfileController::class, 'updateParentalControls'])
            ->name('profiles.update-parental-controls');

        // Update preferences
        Route::post('/preferences', [ProfileController::class, 'updatePreferences'])
            ->name('profiles.update-preferences');
    });
});
