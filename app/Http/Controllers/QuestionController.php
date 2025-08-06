<?php

namespace App\Http\Controllers;

use App\Http\Repositories\QuestionRepository;
use App\Resources\QuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController
{
    public function __construct(private QuestionRepository $questionRepo) {}

    /**
     * Get questions and answers for a methodology
     * 
     * @param int $contextId
     * @return JsonResponse
     */
    public function getMethodologyQuestions(int $contextId): JsonResponse
    {
        $context = 'methodology';
        return $this->getContextQuestions($context, $contextId);
    }

    /**
     * Get questions and answers for a pillar within a specific methodology
     * 
     * @param int $methodologyId
     * @param int $pillarId
     * @return JsonResponse
     */
    public function getPillarQuestionsForMethodology(int $methodologyId, int $pillarId): JsonResponse
    {
        try {
            $questions = $this->questionRepo->getQuestionsByContext('pillar', $pillarId, $methodologyId);
            return response()->json(QuestionResource::collection($questions));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pillar questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get questions and answers for a module within a specific methodology
     * 
     * @param int $methodologyId
     * @param int $moduleId
     * @return JsonResponse
     */
    public function getModuleQuestionsForMethodology(int $methodologyId, int $moduleId): JsonResponse
    {
        try {
            $questions = $this->questionRepo->getQuestionsByContext('module', $moduleId, $methodologyId);
            return response()->json(QuestionResource::collection($questions));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching module questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get questions and answers for a module within a specific pillar of a specific methodology
     * 
     * @param int $methodologyId
     * @param int $pillarId
     * @param int $moduleId
     * @return JsonResponse
     */
    public function getModuleQuestionsForPillarInMethodology(int $methodologyId, int $pillarId, int $moduleId): JsonResponse
    {
        try {
            $questions = $this->questionRepo->getQuestionsByContext('module', $moduleId, $methodologyId, $pillarId);
            return response()->json(QuestionResource::collection($questions));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching module questions for pillar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getContextQuestions(string $context, int $contextId): JsonResponse {
        try{
            $questions = $this->questionRepo->getQuestionsByContext($context, $contextId);

            return response()->json(QuestionResource::collection($questions));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 