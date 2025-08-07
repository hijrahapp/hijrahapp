<?php

namespace App\Services;

use App\Models\UserAnswer;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class ResultCalculationService
{
    /**
     * Calculate methodology results based on user answers
     */
    public function calculateMethodologyResult(int $userId, int $methodologyId): array
    {
        $methodology = Methodology::with(['pillars', 'modules'])->find($methodologyId);

        if (!$methodology) {
            return [];
        }

        // Get total questions and answered questions
        $totalQuestions = $methodology->questions()->count();
        $answeredQuestions = UserAnswer::where('user_id', $userId)
            ->where('context_type', 'methodology')
            ->where('context_id', $methodologyId)
            ->distinct('question_id')
            ->count('question_id');

        if ($answeredQuestions != $totalQuestions) {
            return [];
        }

        $result = [
            'pillars' => [],
            'modules' => [],
            'summary' => [
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ]
        ];

        // Calculate pillar results
        if ($methodology->pillars->isNotEmpty()) {
            foreach ($methodology->pillars as $pillar) {
                try {
                    $pillarResult = $this->calculatePillarResult($userId, $pillar->id, $methodologyId);
                    $result['pillars'][] = [
                        'id' => $pillar->id,
                        'name' => $pillar->name,
                        'percentage' => $pillarResult['percentage'] ?? 0,
                        'summary' => $pillarResult['summary'] ?? []
                    ];
                } catch (\Exception $e) {
                    // Log error and continue with default values
                    \Log::error('Error calculating pillar result: ' . $e->getMessage());
                    $result['pillars'][] = [
                        'id' => $pillar->id,
                        'name' => $pillar->name,
                        'percentage' => 0,
                        'summary' => [
                            'overall_percentage' => 0,
                            'total_questions' => 0,
                            'answered_questions' => 0,
                        ]
                    ];
                }
            }
        }

        // Calculate direct module results (modules not under pillars)
        if ($methodology->modules->isNotEmpty()) {
            foreach ($methodology->modules as $module) {
                try {
                    $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId);
                    $result['modules'][] = [
                        'id' => $module->id,
                        'name' => $module->name,
                        'percentage' => $moduleResult['percentage'] ?? 0,
                        'summary' => $moduleResult['summary'] ?? []
                    ];
                } catch (\Exception $e) {
                    // Log error and continue with default values
                    \Log::error('Error calculating module result: ' . $e->getMessage());
                    $result['modules'][] = [
                        'id' => $module->id,
                        'name' => $module->name,
                        'percentage' => 0,
                        'summary' => [
                            'total_questions' => 0,
                            'answered_questions' => 0,
                            'completion_rate' => 0,
                        ]
                    ];
                }
            }
        }

        // Calculate overall summary
        $allPercentages = [];
        if (!empty($result['pillars'])) {
            $allPercentages = array_merge($allPercentages, array_column($result['pillars'], 'percentage'));
        }
        if (!empty($result['modules'])) {
            $allPercentages = array_merge($allPercentages, array_column($result['modules'], 'percentage'));
        }

        if (!empty($allPercentages)) {
            $result['summary']['overall_percentage'] = round(array_sum($allPercentages) / count($allPercentages), 2);
        }

        $result['summary']['total_questions'] = $totalQuestions;
        $result['summary']['answered_questions'] = $answeredQuestions;

        return $result;
    }

    /**
     * Calculate pillar results based on user answers
     */
    public function calculatePillarResult(int $userId, int $pillarId, int $methodologyId): array
    {
        $pillar = Pillar::with(['modules'])->find($pillarId);

        if (!$pillar) {
            return [];
        }

        // Get total questions and answered questions for this pillar in this methodology
        $totalQuestions = $pillar->questionsForMethodology($methodologyId)->count();
        $answeredQuestions = UserAnswer::where('user_id', $userId)
            ->where('context_type', 'pillar')
            ->where('context_id', $pillarId)
            ->whereIn('question_id', $pillar->questionsForMethodology($methodologyId)->pluck('questions.id'))
            ->distinct('question_id')
            ->count('question_id');

        if ($answeredQuestions != $totalQuestions) {
            return [];
        }

        $result = [
            'modules' => [],
            'summary' => [
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ]
        ];

        // Get modules for this pillar in this specific methodology
        $modules = $pillar->modulesForMethodology($methodologyId)->get();

        // Calculate module results
        foreach ($modules as $module) {
            try {
                $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId, $pillarId);
                $result['modules'][] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'percentage' => $moduleResult['percentage'] ?? 0,
                    'summary' => $moduleResult['summary'] ?? []
                ];
            } catch (\Exception $e) {
                // Log error and continue with default values
                \Log::error('Error calculating module result in pillar: ' . $e->getMessage());
                $result['modules'][] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'percentage' => 0,
                    'summary' => [
                        'total_questions' => 0,
                        'answered_questions' => 0,
                        'completion_rate' => 0,
                    ]
                ];
            }
        }

        // Calculate overall summary
        if (!empty($result['modules'])) {
            $percentages = array_column($result['modules'], 'percentage');
            $result['summary']['overall_percentage'] = round(array_sum($percentages) / count($percentages), 2);
        }

        $result['summary']['total_questions'] = $totalQuestions;
        $result['summary']['answered_questions'] = $answeredQuestions;

        // Add percentage field for methodology calculation
        $result['percentage'] = $result['summary']['overall_percentage'];

        return $result;
    }

    /**
     * Calculate module results based on user answers
     */
    public function calculateModuleResult(int $userId, int $moduleId, int $methodologyId, ?int $pillarId = null): array
    {
        $module = Module::find($moduleId);

        if (!$module) {
            return [];
        }

        // Get questions for this module in the specific context
        $questions = $pillarId
            ? $module->questionsForPillarInMethodology($methodologyId, $pillarId)
            : $module->questionsForMethodology($methodologyId);

        $totalQuestions = $questions->count();

        if ($totalQuestions === 0) {
            return [];
        }

        // Get user answers for this module
        $userAnswers = UserAnswer::where('user_id', $userId)
            ->where('context_type', 'module')
            ->where('context_id', $moduleId)
            ->whereIn('question_id', $questions->pluck('questions.id'))
            ->get();

        $answeredQuestions = $userAnswers->unique('question_id')->count();

        if ($answeredQuestions != $totalQuestions) {
            return [];
        }

        // Calculate percentage (for now, using random values as placeholder)
        $percentage = $answeredQuestions > 0 ? rand(60, 95) : 0; // Random percentage between 60-95 for demo

        return [
            'percentage' => $percentage,
            'summary' => [
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'completion_rate' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0,
            ]
        ];
    }
}
