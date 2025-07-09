<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']); //done
    Route::post('signup', [AuthController::class, 'signUp']); //done
    Route::post('login/firebase', [AuthController::class, 'firebase']); // done // not tested
    Route::middleware(['auth.jwt'])->post('otp/generate', [AuthController::class, 'generateOTP']); //done except MAIL
    Route::middleware(['auth.jwt'])->post('otp/verify', [AuthController::class, 'verifyOTP']); //done
});

Route::prefix('user')->group(function () {
    Route::middleware(['auth.jwt','auth.user','auth.role:Admin'])->post('create', [UserController::class, 'create']);
    Route::middleware(['auth.jwt','auth.user','auth.role:Admin'])->get('all', [UserController::class, 'all']);
    Route::middleware(['auth.jwt','auth.user'])->post('password/reset', [UserController::class, 'resetPassword']); //done
});
