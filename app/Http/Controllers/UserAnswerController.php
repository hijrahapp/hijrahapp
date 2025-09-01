<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserAnswerRepository;
use App\Resources\UserAnswerGroupedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserAnswerController
{
    public function __construct(private UserAnswerRepository $userAnswerRepo) {}

    /**
     * Submit user answers for methodology questions
     *
     * @param Request $request
     * @param int $methodologyId
     * @return JsonResponse
     */
    public function submitMethodologyAnswers(Request $request, int $methodologyId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answerIds' => 'required|array|min:1',
                'answers.*.answerIds.*' => 'integer|exists:answers,id',
                'endQuestions' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');
            $end = (bool)($request->input('endQuestions', false));

            $submittedAnswers = $this->userAnswerRepo->submitMethodologyAnswers($userId, $methodologyId, $answers);

            // Update context status
            $this->userAnswerRepo->upsertContextStatus(
                $userId,
                'methodology',
                $methodologyId,
                null,
                null,
                $end ? 'completed' : 'in_progress'
            );

            return response()->json(null, 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_submitting_methodology_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit user answers for pillar questions within a methodology
     *
     * @param Request $request
     * @param int $methodologyId
     * @param int $pillarId
     * @return JsonResponse
     */
    public function submitPillarAnswers(Request $request, int $methodologyId, int $pillarId): JsonResponse
    {
        try {
            $userId = $request->authUser->id;

            // Validate optional endQuestions flag at top-level
            $endValidator = Validator::make($request->all(), [
                'endQuestions' => 'sometimes|boolean',
            ]);
            if ($endValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => $endValidator->errors()
                ], 422);
            }

            $answers = $request->input('answers');
            $answers = collect($answers)->map(function ($answer) {
                if (str_contains($answer['question_id'], '_')) {
                    $parts = explode('_', $answer['question_id']);
                    $answer['question_id'] = $parts[0];
                    $answer['module_id'] = $parts[1];
                }
                return $answer;
            })->all();

            // Accept grouped answers by module for pillar submissions
            $validator = Validator::make($answers, [
                '*' => 'required|array|min:1',
                '*.module_id' => 'required|integer|exists:modules,id',
                '*.question_id' => 'required|exists:questions,id',
                '*.answerIds' => 'required|array|min:1',
                '*.answerIds.*' => 'integer|exists:answers,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Group answers by module_id
            $groupedAnswers = collect($answers)->groupBy('module_id');
            $end = (bool)($request->input('endQuestions', false));
            foreach ($groupedAnswers as $moduleId => $moduleGroup) {
                $flat = collect($moduleGroup)->map(function ($item) {
                    return [
                        'question_id' => $item['question_id'],
                        'answerIds' => $item['answerIds'],
                    ];
                })->values()->all();

                // Submit as module answers tied to pillar context
                $this->userAnswerRepo->submitPillarModuleAnswers($userId, $methodologyId, $pillarId, $moduleId, $flat);

                // Upsert status per module within this pillar
                $this->userAnswerRepo->upsertContextStatus(
                    $userId,
                    'module',
                    (int)$moduleId,
                    $methodologyId,
                    $pillarId,
                    $end ? 'completed' : 'in_progress'
                );
            }
//            foreach ($groupedAnswers as $moduleGroup) {
//                $moduleId = $moduleGroup['module_id'];
//                $flat = collect($moduleGroup['items'])->map(function ($item) {
//                    return [
//                        'question_id' => $item['question_id'],
//                        'answerIds' => $item['answerIds'],
//                    ];
//                })->values()->all();
//
//                // Submit as module answers tied to pillar context
//                $this->userAnswerRepo->submitPillarModuleAnswers($userId, $methodologyId, $pillarId, $moduleId, $flat);
//            }

            return response()->json(null, 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_submitting_pillar_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit user answers for module questions within a methodology
     *
     * @param Request $request
     * @param int $methodologyId
     * @param int $moduleId
     * @return JsonResponse
     */
    public function submitModuleAnswers(Request $request, int $methodologyId, int $moduleId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answerIds' => 'required|array|min:1',
                'answers.*.answerIds.*' => 'integer|exists:answers,id',
                'endQuestions' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');
            $end = (bool)($request->input('endQuestions', false));

            $submittedAnswers = $this->userAnswerRepo->submitModuleAnswers($userId, $methodologyId, $moduleId, $answers);

            // Update context status for module
            $this->userAnswerRepo->upsertContextStatus(
                $userId,
                'module',
                $moduleId,
                $methodologyId,
                null,
                $end ? 'completed' : 'in_progress'
            );

            return response()->json(null, 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_submitting_module_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit user answers for module questions within a pillar of a methodology
     *
     * @param Request $request
     * @param int $methodologyId
     * @param int $pillarId
     * @param int $moduleId
     * @return JsonResponse
     */
    public function submitPillarModuleAnswers(Request $request, int $methodologyId, int $pillarId, int $moduleId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answerIds' => 'required|array|min:1',
                'answers.*.answerIds.*' => 'integer|exists:answers,id',
                'endQuestions' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');
            $end = (bool)($request->input('endQuestions', false));

            $submittedAnswers = $this->userAnswerRepo->submitPillarModuleAnswers($userId, $methodologyId, $pillarId, $moduleId, $answers);

            // Update context status for pillar-module pair as module context
            $this->userAnswerRepo->upsertContextStatus(
                $userId,
                'module',
                $moduleId,
                $methodologyId,
                $pillarId,
                $end ? 'completed' : 'in_progress'
            );

            return response()->json(null, 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_submitting_pillar_module_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user answers for methodology questions
     *
     * @param int $methodologyId
     * @return JsonResponse
     */
    public function getMethodologyAnswers(Request $request, int $methodologyId): JsonResponse
    {
        try {
            $userId = $request->authUser->id;
            $answers = $this->userAnswerRepo->getUserAnswersGrouped($userId, 'methodology', $methodologyId);

            return response()->json(UserAnswerGroupedResource::collection($answers)->values());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_methodology_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user answers for pillar questions within a methodology
     *
     * @param int $methodologyId
     * @param int $pillarId
     * @return JsonResponse
     */
    public function getPillarAnswers(Request $request, int $methodologyId, int $pillarId): JsonResponse
    {
        try {
            $userId = $request->authUser->id;
            $answers = $this->userAnswerRepo->getUserAnswersGrouped($userId, 'pillar', $pillarId, $methodologyId);

            return response()->json(UserAnswerGroupedResource::collection($answers)->values());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_pillar_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user answers for module questions within a methodology
     *
     * @param int $methodologyId
     * @param int $moduleId
     * @return JsonResponse
     */
    public function getModuleAnswers(Request $request, int $methodologyId, int $moduleId): JsonResponse
    {
        try {
            $userId = $request->authUser->id;
            $answers = $this->userAnswerRepo->getUserAnswersGrouped($userId, 'module', $moduleId, $methodologyId);

            return response()->json(UserAnswerGroupedResource::collection($answers)->values());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_module_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user answers for module questions within a pillar of a methodology
     *
     * @param int $methodologyId
     * @param int $pillarId
     * @param int $moduleId
     * @return JsonResponse
     */
    public function getPillarModuleAnswers(Request $request, int $methodologyId, int $pillarId, int $moduleId): JsonResponse
    {
        try {
            $userId = $request->authUser->id;
            $answers = $this->userAnswerRepo->getUserAnswersGrouped($userId, 'module', $moduleId, $methodologyId, $pillarId);

            return response()->json(UserAnswerGroupedResource::collection($answers)->values());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_pillar_module_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
