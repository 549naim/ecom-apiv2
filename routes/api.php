<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Route;

// Group routes with Sanctum middleware to ensure they are stateful
Route::middleware(['api', EnsureFrontendRequestsAreStateful::class])->group(function () {
    // User authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::put('/profile', [ProfileController::class, 'updateProfile']);

        Route::middleware('is_admin')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{id}', [ProductController::class, 'update']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);
          
            Route::post('/categories', [CategoryController::class, 'store']);
           
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        });
    });

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products/search/{name}', [ProductController::class, 'searchByName']);

    

    // Password management routes
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
});
