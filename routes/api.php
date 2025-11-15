<?php

use App\Http\Controllers\Api\AuthController;
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
