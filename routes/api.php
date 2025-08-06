<?php

use App\Http\Controllers\MethodologyController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('locale')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('signup', [AuthController::class, 'signup']);
        Route::post('login/firebase', [AuthController::class, 'firebaseLogin']);
        Route::post('login/google', [AuthController::class, 'googleAccessTokenLogin']);
        Route::middleware(['auth.jwt','auth.user'])->post('signup/complete', [AuthController::class, 'completeSignup']);
        Route::middleware(['auth.jwt'])->post('otp/verify', [AuthController::class, 'verifyOTP']);
        Route::middleware(['auth.jwt'])->post('otp/resend', [AuthController::class, 'resendOTP']);
    });

    Route::prefix('password')->group(function () {
        Route::post('forget', [PasswordController::class, 'forgetPassword']);
        Route::post('otp/verify', [PasswordController::class, 'verifyOTP']);
        Route::middleware(['auth.jwt','auth.user'])->post('reset', [PasswordController::class, 'resetPassword']);
    });

    Route::prefix('user')->middleware(['auth.jwt','auth.user'])->group(function () {
        Route::middleware(['auth.role:Admin'])->post('', [UserController::class, 'create']);
        Route::middleware(['auth.role:Admin'])->get('all', [UserController::class, 'all']);
        Route::middleware(['auth.role:SuperAdmin'])->delete('', [UserController::class, 'delete']);
    });

    Route::prefix('methodology')->middleware(['auth.jwt','auth.user'])->group(function () {
        Route::get('all', [MethodologyController::class, 'all']);
        Route::get('{id}', [MethodologyController::class, 'get']);
        Route::get('{id}/section/{sectionNumber}', [MethodologyController::class, 'getBySection']);
        Route::get('{id}/questions', [QuestionController::class, 'getMethodologyQuestions']);
        Route::get('{methodologyId}/pillar/{pillarId}/questions', [QuestionController::class, 'getPillarQuestionsForMethodology']);
        Route::get('{methodologyId}/module/{moduleId}/questions', [QuestionController::class, 'getModuleQuestionsForMethodology']);
        Route::get('{methodologyId}/pillar/{pillarId}/module/{moduleId}/questions', [QuestionController::class, 'getModuleQuestionsForPillarInMethodology']);
    });

});
