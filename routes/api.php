<?php


use App\Http\Controllers\AuthController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Route;

// Group routes with Sanctum middleware to ensure they are stateful
Route::middleware(['api', EnsureFrontendRequestsAreStateful::class])->group(function () {
    // User authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Password management routes
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    // Protected user profile route
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
});
