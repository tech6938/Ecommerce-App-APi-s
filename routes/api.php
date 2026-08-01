<?php


use App\Http\Controllers\api\AddressController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\ChatController;
<<<<<<< HEAD
use App\Http\Controllers\Api\OrderController;
=======
use App\Http\Controllers\api\OrderController;
>>>>>>> ddec080 (Fixed currency management from and panel and also in api)
use App\Http\Controllers\api\OtpController;
use App\Http\Controllers\api\PasswordResetController;
use App\Http\Controllers\api\PaymentMethodController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\RatingController;
use App\Http\Controllers\api\SMSController;
use App\Http\Controllers\api\UploadController;
use Illuminate\Support\Facades\Route;

// SMS OTP routes
Route::controller(SMSController::class)->group(function () {
    Route::post('/send-otp',  'sendOtp')->name('send-otp');
    Route::post('/verify-otp',  'verifyOtp')->name('verify-otp');
});

// Email OTP routes
Route::controller(OtpController::class)->group(function () {
    Route::post('/send-verification-otp', 'sendVerificationOtp');
    Route::post('/verify-otp', 'verifyOtp');
    Route::post('/resend-otp', 'resendOtp');
});


// Password reset routes (public)
Route::post('/password/reset-link', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/guest-login', 'guestLoginOrUpdate');
    Route::post('/phone-login', 'loginWithPhone');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/convert-guest', 'convertGuestToRegistered');
        Route::get('/profile', 'getProfile');
        Route::post('/update/profile', 'updateProfile');
        Route::post('/change-password', 'changePassword');
        Route::post('/fcm_token', 'fcm_token');
    });

    //
    Route::controller(ProductController::class)->group(function () {
        // banners
        Route::get('/banners', 'banners');
        // categories
        Route::get('/categories', 'categories');
        // products
        Route::get('/products', 'products');
        Route::get('/products/liked', 'likedProducts');
        Route::get('/products/search', 'autocomplete');
        Route::get('/liked-products/search', 'autocompleteLikedProducts');
        Route::get('/products/{id}', 'productDetails');
        Route::post('/products/{id}/like', 'toggleLike');
        Route::get('/recent-viewed-products', 'recentViewdProducts');
        Route::get('/products-by-category', 'productsByCategory');
        Route::get('/category/products', 'categoriesWithStats');
        // Coupons
        Route::get('/coupons', 'couponsList');
        Route::get('/coupons/search', 'autocompleteCoupons');
        // COD Charges
        Route::get('/cod-charges', 'codCharges');
    });

    Route::controller(CartController::class)->group(function () {
        Route::get('/cart', 'index');
        Route::get('/count', 'count');
        Route::post('/add', 'add');
        Route::delete('/clear', 'clear');

        Route::prefix('items/{cartItemId}')->group(function () {
            Route::post('/', 'update');
            Route::post('/increase', 'increase');
            Route::post('/decrease', 'decrease');
            Route::delete('/remove', 'remove');
        });
    });

    Route::controller(AddressController::class)->prefix('addresses')->group(function () {
        // Resource routes
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::post('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        // Custom routes
        Route::get('/default/show', 'getDefault');
        Route::post('/{id}/default', 'setDefault');
    });

    Route::controller(RatingController::class)->group(function () {
        Route::post('/rating/add', 'addOrUpdateRating');
        Route::delete('/rating/delete/{id}', 'deleteRating');
        Route::get('/my-ratings', 'myRatings');

        // Rating details (public, but for single rating)
        Route::get('/rating/{id}', 'showRating');
        Route::get('/products/{productId}/ratings', 'productRatings');
    });

    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/summary', 'summary');
        Route::get('/{id}', 'show');
        Route::post('/{id}/cancel', 'cancel');
    });

    Route::prefix('payment-methods')->controller(PaymentMethodController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/cod', 'getCOD');
        Route::get('/online', 'getOnlineMethods');
        Route::get('/{code}', 'show');
        Route::get('/{code}/credentials', 'getCredentials');
        Route::post('/payment-tracking/{orderId}', 'paymentTracking');
    });

    // Customer routes
    Route::post('/start', [ChatController::class, 'startConversation']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);

    // Conversation specific routes
    Route::prefix('{conversationId}')->group(function () {
        Route::get('/messages', [ChatController::class, 'getMessages']);
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::post('/typing', [ChatController::class, 'sendTyping']);
        Route::post('/read', [ChatController::class, 'markAsRead']);
        Route::post('/close', [ChatController::class, 'closeConversation']);
    });

    // Customer routes
    Route::post('/start', [ChatController::class, 'startConversation']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);

    // Conversation specific routes
    Route::prefix('{conversationId}')->group(function () {
        Route::get('/messages', [ChatController::class, 'getMessages']);
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::post('/typing', [ChatController::class, 'sendTyping']);
        Route::post('/read', [ChatController::class, 'markAsRead']);
        Route::post('/close', [ChatController::class, 'closeConversation']);
    });

    Route::post('/upload', [UploadController::class, 'upload']);
    Route::get('/upload/list', [UploadController::class, 'list']);
});
