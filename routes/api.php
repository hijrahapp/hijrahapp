<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MethodologyController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserAnswerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('locale')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('signup', [AuthController::class, 'signup']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('login/firebase', [AuthController::class, 'firebaseLogin']);
        Route::post('login/google', [AuthController::class, 'googleAccessTokenLogin']);
        Route::middleware(['auth.jwt', 'auth.user'])->post('signup/complete', [AuthController::class, 'completeSignup']);
        Route::middleware(['auth.jwt'])->post('otp/verify', [AuthController::class, 'verifyOTP']);
        Route::middleware(['auth.jwt'])->post('otp/resend', [AuthController::class, 'resendOTP']);
    });

    Route::prefix('password')->group(function () {
        Route::post('forget', [PasswordController::class, 'forgetPassword']);
        Route::post('otp/verify', [PasswordController::class, 'verifyOTP']);
        Route::middleware(['auth.jwt', 'auth.user'])->post('reset', [PasswordController::class, 'resetPassword']);
    });

    Route::prefix('user')->middleware(['auth.jwt', 'auth.user'])->group(function () {
        Route::middleware(['auth.role:Admin'])->post('', [UserController::class, 'create']);
        Route::middleware(['auth.role:Admin'])->get('all', [UserController::class, 'all']);
        Route::middleware(['auth.role:SuperAdmin'])->delete('', [UserController::class, 'delete']);
    });

    Route::prefix('methodology')->middleware(['auth.jwt', 'auth.user'])->group(function () {
        // Methodology endpoints
        Route::get('all', [MethodologyController::class, 'all']);
        Route::get('{methodologyId}', [MethodologyController::class, 'get']);
        Route::get('{methodologyId}/section/{sectionNumber}', [MethodologyController::class, 'getBySection']);
        Route::get('{methodologyId}/pillar/{pillarId}', [MethodologyController::class, 'getPillar']);
        Route::get('{methodologyId}/module/{moduleId}', [MethodologyController::class, 'getModule']);
        Route::get('{methodologyId}/pillar/{pillarId}/module/{moduleId}', [MethodologyController::class, 'getPillarModule']);

        // Questions endpoints
        Route::get('{methodologyId}/questions', [QuestionController::class, 'getMethodologyQuestions']);
        Route::get('{methodologyId}/pillar/{pillarId}/questions', [QuestionController::class, 'getPillarQuestionsForMethodology']);
        Route::get('{methodologyId}/module/{moduleId}/questions', [QuestionController::class, 'getModuleQuestionsForMethodology']);
        Route::get('{methodologyId}/pillar/{pillarId}/module/{moduleId}/questions', [QuestionController::class, 'getModuleQuestionsForPillarInMethodology']);

        // Submit answers endpoints
        Route::post('{methodologyId}/answers', [UserAnswerController::class, 'submitMethodologyAnswers']);
        Route::post('{methodologyId}/pillar/{pillarId}/answers', [UserAnswerController::class, 'submitPillarAnswers']);
        Route::post('{methodologyId}/module/{moduleId}/answers', [UserAnswerController::class, 'submitModuleAnswers']);
        Route::post('{methodologyId}/pillar/{pillarId}/module/{moduleId}/answers', [UserAnswerController::class, 'submitPillarModuleAnswers']);

        // Get answers endpoints
        Route::get('{methodologyId}/answers', [UserAnswerController::class, 'getMethodologyAnswers']);
        Route::get('{methodologyId}/pillar/{pillarId}/answers', [UserAnswerController::class, 'getPillarAnswers']);
        Route::get('{methodologyId}/module/{moduleId}/answers', [UserAnswerController::class, 'getModuleAnswers']);
        Route::get('{methodologyId}/pillar/{pillarId}/module/{moduleId}/answers', [UserAnswerController::class, 'getPillarModuleAnswers']);
    });

    Route::prefix('programs')->middleware(['auth.jwt', 'auth.user'])->group(function () {
        // Public endpoints (authenticated users)
        Route::get('all', [ProgramController::class, 'all']);
        Route::get('{programId}', [ProgramController::class, 'get']);
        Route::get('{programId}/methodology/{methodologyId}', [ProgramController::class, 'getWithModulesForMethodology']);
        Route::get('methodology/{methodologyId}/available-modules', [ProgramController::class, 'getAvailableModules']);

        // Objectives endpoints (public)
        Route::prefix('{programId}/objectives')->group(function () {
            Route::get('', [ObjectiveController::class, 'getByProgram']);
            Route::get('{objectiveId}', [ObjectiveController::class, 'get']);
        });

        // Global objectives endpoints (public)
        Route::prefix('objectives')->group(function () {
            Route::get('all', [ObjectiveController::class, 'all']);
            Route::get('statistics', [ObjectiveController::class, 'getStatistics']);
            Route::get('type/{type}', [ObjectiveController::class, 'getByType']);
        });

        // Admin-only endpoints
        Route::middleware(['auth.role:Admin'])->group(function () {
            // Program management
            Route::post('', [ProgramController::class, 'create']);
            Route::put('{programId}', [ProgramController::class, 'update']);
            Route::delete('{programId}', [ProgramController::class, 'delete']);
            Route::post('{programId}/modules/attach', [ProgramController::class, 'attachModule']);
            Route::delete('{programId}/modules/detach', [ProgramController::class, 'detachModule']);
            Route::put('{programId}/modules/scores', [ProgramController::class, 'updateModuleScores']);

            // Objectives management
            Route::prefix('{programId}/objectives')->group(function () {
                Route::post('', [ObjectiveController::class, 'create']);
                Route::put('{objectiveId}', [ObjectiveController::class, 'update']);
                Route::delete('{objectiveId}', [ObjectiveController::class, 'delete']);
                Route::post('{objectiveId}/duplicate', [ObjectiveController::class, 'duplicate']);
                Route::put('reorder', [ObjectiveController::class, 'reorder']);
            });
        });
    });

});
