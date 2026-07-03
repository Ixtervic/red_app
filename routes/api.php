<?php

use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('users', UserController::class);
Route::post('users/{user}/images', [UserController::class, 'addImages']);
Route::delete('images/{image}', [ImageController::class, 'destroy']);
