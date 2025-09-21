<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\LiabilityController;
use App\Http\Controllers\MethodologyController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StepController;
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

    Route::prefix('program')->middleware(['auth.jwt', 'auth.user'])->group(function () {
        // Program endpoints
        Route::get('suggested', [ProgramController::class, 'getSuggestedPrograms']);
        Route::get('suggested/filters', [ProgramController::class, 'getSuggestedProgramsFilters']);
        Route::get('my', [ProgramController::class, 'getMyPrograms']);
        Route::get('my/filters', [ProgramController::class, 'getMyProgramsFilters']);
        Route::get('{programId}', [ProgramController::class, 'get']);

        // Steps endpoints
        Route::get('{programId}/steps', [StepController::class, 'getByProgram']);
        Route::get('{programId}/step/{stepId}', [StepController::class, 'get']);

        // Step progress endpoints
        Route::post('{programId}/step/{stepId}/start', [StepController::class, 'startStep']);
        Route::post('{programId}/step/{stepId}/complete', [StepController::class, 'completeStep']);
        Route::post('{programId}/step/{stepId}/challenge', [StepController::class, 'updateChallengeProgress']);

        // User interaction endpoints
        Route::post('{programId}/start', [ProgramController::class, 'startProgram']);
        Route::post('{programId}/complete', [ProgramController::class, 'completeProgram']);
        Route::post('{programId}/reset', [ProgramController::class, 'resetProgram']);

        // Feedback endpoints
        Route::get('feedback/form', [ProgramController::class, 'getFeedbackForm']);
        Route::post('{programId}/feedback', [ProgramController::class, 'submitFeedback']);
        Route::get('{programId}/feedback/stats', [ProgramController::class, 'getFeedbackStats']);
    });

    Route::prefix('liability')->middleware(['auth.jwt', 'auth.user'])->group(function () {
        // Liability endpoints
        Route::get('my', [LiabilityController::class, 'getMyLiabilities']);
        Route::get('my/filters', [LiabilityController::class, 'getMyLiabilitiesFilters']);
        Route::get('{liabilityId}', [LiabilityController::class, 'get']);

        // Todo progress endpoints
        Route::post('{liabilityId}/todo/update', [LiabilityController::class, 'updateTodo']);
        Route::post('{liabilityId}/complete', [LiabilityController::class, 'completeLiability']);
    });

    Route::prefix('interest')->middleware(['auth.jwt', 'auth.user'])->group(function () {
        // Get all interests (public endpoint)
        Route::get('all', [InterestController::class, 'all']);

        // User interests
        Route::get('user', [InterestController::class, 'getUserInterests']);
        Route::post('user/update', [InterestController::class, 'updateUserInterests']);
    });

});
