<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
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
Route::resource('cart', CartController::class)
    ->only(['index'])
    ->middleware(['auth:sanctum']);

Route::resource('cart', CartController::class)
    ->only(['store', 'show', 'update', 'destroy'])
    ->middleware(['auth:sanctum']);

Route::resource('cart', CartController::class)
    ->only(['update', 'destroy'])
    ->middleware(['auth:sanctum']);

// Order Routes
Route::resource('orders', OrderController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'admin_or_owner']);
