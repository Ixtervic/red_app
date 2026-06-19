<?php

use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('users', UserController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->names('api.user');

Route::resource('images', ImageController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->names('api.image');
