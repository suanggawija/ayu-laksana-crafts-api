<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout')->middleware('auth:sanctum');
    Route::get('/user', 'user')->middleware('auth:sanctum');
});

// User Routes
Route::resource('users', UserController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->middleware('auth:sanctum');

// Product Routes
Route::get('products/most-ordered', [ProductController::class, 'mostOrdered']);

Route::resource('products', ProductController::class)
    ->only(['index', 'show']);

Route::resource('products', ProductController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'admin']);


// Product Category Routes
Route::resource('product-categories', ProductCategoryController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'admin']);

// Cart Routes
Route::resource('carts', CartController::class)
    ->only(['index', 'update ', ' destroy '])
    ->middleware(['auth:sanctum']);

Route::resource('carts', CartController::class)
    ->only(['store', 'show', 'update', 'destroy'])
    ->middleware(['auth:sanctum']);

// Order Routes
Route::resource('orders', OrderController::class)
    ->only(['index', 'store', 'show'])
    ->middleware(['auth:sanctum']);

Route::controller(OrderController::class)
    ->prefix('orders/{order}')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::post('/update_delivered_at', 'update_delivered_at');
        Route::post('/update_completed_at', 'update_completed_at');
        Route::post('/update_cencelled_at', 'update_cencelled_at');

        Route::post('/update_payment_to_paid', 'update_payment_to_paid');
        Route::post('/update_payment_to_failed', 'update_payment_to_failed');
    });
