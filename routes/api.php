<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Notification\NotificationTestController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/generate-otp', [AuthController::class, 'sendOtp']);
    Route::post('/validate-otp', [AuthController::class, 'validateOtp']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('notification')->group(function () {
        Route::post('/broadcast/test', [NotificationTestController::class, 'broadcast']);
        Route::post('/private/test', [NotificationTestController::class, 'private']);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});
