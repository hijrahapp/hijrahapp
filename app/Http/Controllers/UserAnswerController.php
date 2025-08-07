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
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');

            $submittedAnswers = $this->userAnswerRepo->submitMethodologyAnswers($userId, $methodologyId, $answers);

            return response()->json([
                'success' => true,
                'message' => 'Methodology answers submitted successfully',
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting methodology answers',
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
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');

            $submittedAnswers = $this->userAnswerRepo->submitPillarAnswers($userId, $methodologyId, $pillarId, $answers);

            return response()->json([
                'success' => true,
                'message' => 'Pillar answers submitted successfully',
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting pillar answers',
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
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');

            $submittedAnswers = $this->userAnswerRepo->submitModuleAnswers($userId, $methodologyId, $moduleId, $answers);

            return response()->json([
                'success' => true,
                'message' => 'Module answers submitted successfully',
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting module answers',
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
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->authUser->id;
            $answers = $request->input('answers');

            $submittedAnswers = $this->userAnswerRepo->submitPillarModuleAnswers($userId, $methodologyId, $pillarId, $moduleId, $answers);

            return response()->json([
                'success' => true,
                'message' => 'Pillar module answers submitted successfully',
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting pillar module answers',
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
                'message' => 'Error fetching methodology answers',
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
                'message' => 'Error fetching pillar answers',
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
                'message' => 'Error fetching module answers',
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
                'message' => 'Error fetching pillar module answers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 