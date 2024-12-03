<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// AUTH ROUTES
Route::controller(AuthController::class)->group(function () {

    Route::middleware('guest')->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
    });

    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});
