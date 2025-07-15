<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']); //done
    Route::post('signup', [AuthController::class, 'signup']); //done
    Route::post('login/firebase', [AuthController::class, 'firebaseLogin']); // done
    Route::middleware(['auth.jwt','auth.user'])->post('signup/complete', [AuthController::class, 'completeSignup']); // done
    Route::middleware(['auth.jwt'])->post('otp/verify', [AuthController::class, 'verifyOTP']); //done
    Route::middleware(['auth.jwt'])->post('otp/resend', [AuthController::class, 'resendOTP']); //done
});

Route::prefix('password')->group(function () {
    Route::post('forget', [PasswordController::class, 'forgetPassword']); //done
    Route::post('otp/verify', [PasswordController::class, 'verifyOTP']); //done
    Route::middleware(['auth.jwt','auth.user'])->post('reset', [PasswordController::class, 'resetPassword']); //done
});

Route::prefix('user')->middleware(['auth.jwt','auth.user','auth.role:Admin'])->group(function () {
    Route::post('create', [UserController::class, 'create']);
    Route::get('all', [UserController::class, 'all']);
});
