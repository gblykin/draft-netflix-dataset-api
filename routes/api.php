<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;

Route::middleware('api')->group(function () {
    // API Resources - автоматически создает все RESTful маршруты
    Route::apiResource('movies', MovieController::class)->only(['index', 'show']);
    Route::apiResource('users', UserController::class)->only(['index', 'show']);
    Route::apiResource('reviews', ReviewController::class);
});

