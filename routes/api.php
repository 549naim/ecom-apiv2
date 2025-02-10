<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BannerController;

// Group routes with Sanctum middleware to ensure they are stateful
Route::middleware(['api', EnsureFrontendRequestsAreStateful::class])->group(function () {
    // User authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::put('/profile', [ProfileController::class, 'updateProfile']);
        Route::post('/reviews', [ReviewController::class, 'store']); // Add a review
        Route::post('/cart/apply-coupon', [CouponController::class, 'applyCoupon']);
        Route::post('/checkout', [CheckoutController::class, 'checkout']);
        Route::get('/user/orders', [OrderController::class, 'userOrders']);
        Route::get('/user/orders/{id}', [OrderController::class, 'orderDetails']);

        // Create a new coupon
      

        // Get all coupons
        Route::get('/coupons', [CouponController::class, 'index']);

        // Get a specific coupon
        Route::get('/coupons/{id}', [CouponController::class, 'show']);

      

        Route::middleware('is_admin')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{id}', [ProductController::class, 'update']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);
          
            Route::post('/categories', [CategoryController::class, 'store']);
           
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
            Route::post('/coupons', [CouponController::class, 'store']);
              // Update a coupon
              Route::put('/coupons/{id}', [CouponController::class, 'update']);


            // Delete a coupon
            Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);
            Route::get('/orders', [OrderController::class, 'index']);          // Get all orders (Admin)
            Route::get('/orders/{id}', [OrderController::class, 'show']);      // Get order details
            Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']); // Update order status
            

            Route::prefix('banners')->group(function () {
                Route::get('/', [BannerController::class, 'index']);
                Route::post('/', [BannerController::class, 'store']);
                Route::get('{id}', [BannerController::class, 'show']);
                Route::put('{id}', [BannerController::class, 'update']);
                Route::delete('{id}', [BannerController::class, 'destroy']);
            });

           
           
        });
    });

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products/search/{name}', [ProductController::class, 'searchByName']);
    Route::get('/products/category/{categoryId}', [ProductController::class, 'filterByCategory']);
    Route::post('/products/filter', [ProductController::class, 'filterByPriceAndSort']);
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);



    

    // Password management routes
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
});
