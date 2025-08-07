<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserAnswerRepository;
use App\Resources\UserAnswerGroupedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

            $submittedAnswers = $this->userAnswerRepo->submitMethodologyAnswers($userId, $methodologyId, $answers);

            return response()->json([
                'success' => true,
                'message' => __('messages.methodology_answers_submitted_successfully'),
            ], 201);

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
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answerIds' => 'required|array|min:1',
                'answers.*.answerIds.*' => 'integer|exists:answers,id',
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

            $submittedAnswers = $this->userAnswerRepo->submitPillarAnswers($userId, $methodologyId, $pillarId, $answers);

            return response()->json([
                'success' => true,
                'message' => __('messages.pillar_answers_submitted_successfully'),
            ], 201);

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

            $submittedAnswers = $this->userAnswerRepo->submitModuleAnswers($userId, $methodologyId, $moduleId, $answers);

            return response()->json([
                'success' => true,
                'message' => __('messages.module_answers_submitted_successfully'),
            ], 201);

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

            $submittedAnswers = $this->userAnswerRepo->submitPillarModuleAnswers($userId, $methodologyId, $pillarId, $moduleId, $answers);

            return response()->json([
                'success' => true,
                'message' => __('messages.pillar_module_answers_submitted_successfully'),
            ], 201);

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

            return response()->json(UserAnswerGroupedResource::collection($answers));

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

            return response()->json(UserAnswerGroupedResource::collection($answers));

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

            return response()->json(UserAnswerGroupedResource::collection($answers));

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

            return response()->json(UserAnswerGroupedResource::collection($answers));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_pillar_module_answers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 