<?php

namespace App\Http\Controllers;

use App\Http\Repositories\StepRepository;
use App\Resources\StepDetailedResource;
use App\Resources\StepResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StepController
{
    public function __construct(
        private StepRepository $stepRepo
    ) {}

    public function get(Request $request, int $programId, int $stepId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $step = $this->stepRepo->findById($stepId);

            if (! $step) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.step_not_found'),
                ], 404);
            }

            // Load user progress if user is authenticated
            if ($user) {
                $step->load(['userProgress' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }]);
            }

            return response()->json(new StepDetailedResource($step));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_step'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // TODO: ADD Filter request param for type
    public function getByProgram(Request $request, int $programId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $steps = $this->stepRepo->getStepsWithProgressForUser($user->id, $programId);

            return response()->json(StepResource::collection($steps));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_program_steps'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function completeStep(Request $request, int $programId, int $stepId): JsonResponse
    {
        try {
            $user = $request->authUser;
            $step = $this->stepRepo->findById($stepId);

            if (! $step) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.step_not_found'),
                ], 404);
            }

            // Validate type-specific data
            $validationRules =
                $this->getValidationRulesForStepType($step->type);
            if (! empty($validationRules)) {
                $request->validate($validationRules);
            }

            // Complete the step
            $success = $this->stepRepo->completeStep(
                $user->id,
                $programId,
                $stepId,
                $request->all()
            );

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_completing_step'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.step_completed_successfully'),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_completing_step'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function startStep(Request $request, int $programId, int $stepId): JsonResponse
    {
        try {
            $user = $request->authUser;

            $success =
                $this->stepRepo->startStep($user->id, $programId,
                    $stepId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_starting_step'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.step_started_successfully'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_starting_step'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateChallengeProgress(Request $request, int $programId, int $stepId): JsonResponse
    {
        try {
            $user = $request->authUser;

            // Validate the request
            $request->validate([
                'challenges_done' => 'required|integer|min:0',
            ]);

            $result = $this->stepRepo->updateChallengeProgress(
                $user->id,
                $programId,
                $stepId,
                $request->input('challenges_done')
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.challenge_progress_updated'),
                'data' => $result['data'],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_challenge_progress'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get validation rules based on step type
     */
    private function getValidationRulesForStepType(string $type): array
    {
        return match ($type) {
            'journal' => [
                'thought' => 'required|string|max:2000',
            ],
            'quiz' => [
                'score' => 'required|integer|min:0',
            ],
            'challenge' => [
                'challenges_done' => 'required|integer|min:0',
                'percentage' => 'required|numeric|min:0|max:100',
            ],
            default => [],
        };
    }
}
