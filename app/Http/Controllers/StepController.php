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

            // Load relationships
            $step->load(['program']);

            // Load questions with answers for quiz-type steps
            if ($step->type === 'quiz') {
                $step->load(['questions.answers']);
            }

            // Load user progress if user is authenticated
            if ($user) {
                $step->load(['userProgress' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }]);

                // Load user answers for quiz-type steps
                if ($step->type === 'quiz') {
                    $userAnswers = \App\Models\UserAnswer::where('user_id', $user->id)
                        ->where('context_type', 'module')
                        ->where('context_id', $step->id)
                        ->with(['question', 'answer'])
                        ->get()
                        ->groupBy('question_id');

                    $step->setRelation('userAnswers', $userAnswers);
                }
            }

            $resource = new StepDetailedResource($step);

            return response()->json($resource->toArray($request));
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
            $result = $this->stepRepo->completeStep(
                $user->id,
                $programId,
                $stepId,
                $request->all()
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? __('messages.error_completing_step'),
                ], 400);
            }

            $response = [
                'success' => true,
                'message' => __('messages.step_completed_successfully'),
            ];

            // Add quiz-specific data if it's a quiz step
            if ($step->type === 'quiz' && isset($result['data'])) {
                $response['data'] = $result['data'];
            }

            return response()->json($response, 200);
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
                'challenge_id' => 'required|integer|min:1',
                'is_completed' => 'required|boolean',
            ]);

            $result = $this->stepRepo->toggleChallengeProgress(
                $user->id,
                $programId,
                $stepId,
                $request->input('challenge_id'),
                $request->input('is_completed')
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
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answer_id' => 'required|integer|exists:answers,id',
            ],
            default => [],
        };
    }
}
