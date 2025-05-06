<?php

use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::resource('users', UserController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::resource('products', ProductController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);

Route::resource('product-categories', ProductCategoryController::class)->only([
    'index',
    'store',
    'show',
    'update',
    'destroy'
]);
