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
        try{
            $questions = $this->questionRepo->getQuestionsByContext('methodology', $contextId);

            return response()->json($questions);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_questions'),
                'error' => $e->getMessage()
            ], 500);
        }
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
            // Return questions grouped by modules under this pillar for the methodology
            $questions = $this->questionRepo->getQuestionsByContext('pillar', $pillarId, $methodologyId);
            return response()->json($questions);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_pillar_questions'),
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
            return response()->json($questions);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_module_questions'),
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
            return response()->json($questions);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_fetching_module_questions_for_pillar'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
