<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryAttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;







// dash
Route::middleware([AuthMiddleware::class])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
//system setting
Route::get('/system-setting', [SystemSettingController::class, 'index'])->name('system.setting');
Route::post('/system-setting/update', [SystemSettingController::class, 'update'])->name('system.setting.update');

Route::resource('banner', BannerController::class);
Route::get(
    'banner-status/{id}',
    [BannerController::class, 'changeStatus']
)->name('banner.status');

// Route::get('/products/create', [ProductController::class, 'create'])
//     ->name('products.create');

// Route::post('/products/store', [ProductController::class, 'store'])
//     ->name('products.store');

Route::get('product', [ProductController::class, 'index'])->name('products.index');

Route::get('products/create', [ProductController::class, 'create'])->name('products.create');

Route::post('products/store', [ProductController::class, 'store'])->name('products.store');

Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');

    Route::resource('attributes', AttributeController::class);

Route::get('category-attributes', [CategoryAttributeController::class, 'index'])
    ->name('category.attributes.index');

Route::get('category-attributes/create', [CategoryAttributeController::class, 'create'])
    ->name('category.attributes.create');

Route::post('category-attributes', [CategoryAttributeController::class, 'store'])
    ->name('category.attributes.store');

    Route::get(
    'products/change-status/{id}',
    [ProductController::class, 'changeStatus']
)->name('products.change.status');


    Route::post(
    '/categories/{category}/attributes',
    [CategoryAttributeController::class, 'storeAttribute']
)->name('categories.attributes.store');

// Existing attribute mein naya option add karo
Route::post(
    '/categories/{category}/attributes/{attribute}/options',
    [CategoryAttributeController::class, 'storeOption']
)->name('categories.attributes.options.store');

Route::delete(
    '/categories/{category}/attributes/{attribute}',
    [CategoryAttributeController::class, 'destroyAttribute']
)->name('categories.attributes.destroy');

Route::get('/category', [CategoryController::class, 'index'])
    ->name('category.index');
Route::get('/category/create', [CategoryController::class, 'create'])
    ->name('category.create');
Route::post('/category/store', [CategoryController::class, 'store'])
    ->name('category.store');
Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])
    ->name('category.edit');
Route::put('/category/update/{id}', [CategoryController::class, 'update'])
    ->name('category.update');
Route::delete('/category/delete/{id}', [CategoryController::class, 'destroy'])
    ->name('category.destroy');
Route::get('/category/status/{id}', [CategoryController::class, 'changeStatus'])
    ->name('category.status');
});

Route::resource('coupon', CouponController::class);
Route::get('coupon/change-status/{id}',[CouponController::class, 'changeStatus']
)->name('coupon.change.status');
Route::resource('currency', CurrencyController::class);
Route::post('/currency/status', [CurrencyController::class, 'changeStatus'])->name('currency.status');

Route::controller(AuthController::class)->group(function () {

    // Login
    Route::get('/', 'login')->name('login');
    Route::post('/match-login', 'match_login')->name('match-login');
    // Signup
    Route::get('/signup', 'signup')->name('signup');
    Route::post('/insert-signup', 'insert_signup')->name('insert-signup');
    // Logout
    Route::post('/logout', 'logout')->name('logout');
    // Forgot password
    Route::get('/forget', 'forget')->name('forget');
    Route::post('/forget-message', 'forget_message')->name('forget_message');
    // OTP
    Route::get('/otp', 'otp')->name('otp');
    Route::post('/matching-route', 'matching_route')->name('matching_route');
    // Reset password
    Route::get('/reset', 'reset')->name('reset');
    Route::post('/reset-password', 'update_password')->name('reset_password');
});

