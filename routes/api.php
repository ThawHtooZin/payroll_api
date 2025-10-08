<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CalendarController;

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth-related protected routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
    
    // General user route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Admin-only routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Calendar routes - Get ALL the calendar data! XD
        Route::get('/calendar/month', [CalendarController::class, 'getMonthCalendar']);
        Route::get('/calendar/year', [CalendarController::class, 'getYearCalendar']);
    });
});
