<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Platform\YouTubeConnectionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::middleware('throttle:auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Parameter names must stay {id}/{hash}: the framework's VerifyEmail
// notification generates the signed URL with exactly these parameters.
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:auth'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'currentUser']);
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:auth');
});

// Platform connections require a verified email address.
Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('/youtube/connect', [YouTubeConnectionController::class, 'connect']);
    Route::delete('/youtube/disconnect', [YouTubeConnectionController::class, 'disconnect']);
});

// Hit by Google's redirect; protected by the one-time state token, not a session.
Route::get('/youtube/callback', [YouTubeConnectionController::class, 'callback']);
