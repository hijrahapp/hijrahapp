<?php

namespace App\Services;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\UserContextStatus;
use Illuminate\Support\Facades\DB;

class ResultCalculationOptimizedService
{
    public function __construct(
        protected ?ContextStatusService $contextStatusService = null,
    ) {
        $this->contextStatusService = $contextStatusService ?? new ContextStatusService();
    }
    /**
     * Calculate methodology results honoring simple/dynamic modes.
     * Keeps the same return structure as ResultCalculationService.
     */
    public function calculateMethodologyResult(int $userId, int $methodologyId)
    {
        $methodology = Methodology::with(['pillars', 'modules'])->find($methodologyId);
        if (!$methodology) {
            return null;
        }

        $result = [
            'pillars' => [],
            'modules' => [],
            'summary' => [
                'text' => __('messages.lorem_ipsum'),
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ]
        ];

        // Methodology-level questions are always simple.
        $overall = $this->computeSimplePercentageForMethodology($userId, $methodologyId);
        if($overall['answered_questions'] === 0) {
            return null;
        }

        $result['summary']['overall_percentage'] = $overall['percentage'];
        $result['summary']['total_questions'] = $overall['total_questions'];
        $result['summary']['answered_questions'] = $overall['answered_questions'];

        if ($methodology->type === 'complex' || $methodology->type === 'twoSection') {
            $isTwoSection = ($methodology->type === 'twoSection');
            $pillars = $isTwoSection
                ? $methodology->pillars()->wherePivot('section', 'first')->get()
                : $methodology->pillars;

            $pillarWeightedSum = 0.0;
            $pillarTotalWeight = 0.0;

            foreach ($pillars as $pillar) {
                $allModulesCompleted = $this->areAllPillarModulesCompleted($userId, $methodologyId, $pillar->id);

                if($allModulesCompleted) {
                    $pillarResult = $this->calculatePillarResult($userId, $pillar->id, $methodologyId);
                }else {
                    $pillarResult = $this->computeDynamicLikePercentageForMethodologyItem($userId, $methodologyId, 'pillar', $pillar->id);
                }

                $pillarPercentage = $pillarResult['percentage'] ?? 0;
                $pillarWeight = (float)($pillar->pivot->weight ?? 0.0);
                
                // Add to weighted calculation
                $pillarWeightedSum += ($pillarPercentage * $pillarWeight);
                $pillarTotalWeight += $pillarWeight;

                $result['pillars'][] = [
                    'id' => $pillar->id,
                    'name' => $pillar->name,
                    'description' => $pillar->description,
                    'definition' => $pillar->definition,
                    'objectives' => $pillar->objectives,
                    'percentage' => $pillarPercentage,
                    'weight' => $pillarWeight,
                    'summary' => $pillarResult['summary'] ?? [
                        'text' => __('messages.lorem_ipsum'),
                        'total_questions' => 0,
                        'answered_questions' => 0,
                        'completion_rate' => 0,
                    ],
                ];
            }

            // Update overall percentage with weighted average for pillars
            if ($pillarTotalWeight > 0.0) {
                $result['summary']['overall_percentage'] = round($pillarWeightedSum / $pillarTotalWeight, 2);
            }
        } else {
            // Simple methodology: produce module breakdown with weighted averages
            $moduleWeightedSum = 0.0;
            $moduleTotalWeight = 0.0;

            foreach ($methodology->modules as $module) {
                $moduleCompleted = $this->isModuleContextCompleted($userId, $module->id, $methodologyId, null);
                
                if ($moduleCompleted) {
                    $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId, null);
                } else {
                    $moduleResult = $this->computeDynamicLikePercentageForMethodologyItem($userId, $methodologyId, 'module', $module->id);
                }

                // if (!$moduleResult) {
                    // Fallback to methodology questions assigned to this module (item_id = module_id)
                    // $moduleResult = $this->computeDynamicLikePercentageForMethodologyItem($userId, $methodologyId, 'module', $module->id);
                // }

                $modulePercentage = $moduleResult['percentage'] ?? 0;
                $moduleWeight = (float)($module->pivot->weight ?? 0.0);
                
                // Add to weighted calculation
                $moduleWeightedSum += ($modulePercentage * $moduleWeight);
                $moduleTotalWeight += $moduleWeight;

                $result['modules'][] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'description' => $module->description,
                    'definition' => $module->definition,
                    'objectives' => $module->objectives,
                    'percentage' => $modulePercentage,
                    'weight' => $moduleWeight,
                    'summary' => $moduleResult['summary'] ?? [
                        'text' => __('messages.lorem_ipsum'),
                        'total_questions' => 0,
                        'answered_questions' => 0,
                        'completion_rate' => 0,
                    ],
                ];
            }

            // Update overall percentage with weighted average for modules
            if ($moduleTotalWeight > 0.0) {
                $result['summary']['overall_percentage'] = round($moduleWeightedSum / $moduleTotalWeight, 2);
            }
        }

        return $result;
    }

    /**
     * Calculate section results for two-section methodology (average of pillar results).
     */
    public function calculateSectionResult(int $userId, int $methodologyId, int $sectionNumber)
    {
        $methodology = Methodology::with(['pillars'])->find($methodologyId);
        if (!$methodology || $methodology->type !== 'twoSection') {
            return null;
        }

        $section = $sectionNumber === 2 ? 'second' : 'first';
        $pillars = $methodology->pillars()->wherePivot('section', $section)->get();
        if ($pillars->isEmpty()) {
            return null;
        }

        $result = [
            'pillars' => [],
            'summary' => [
                'text' => __('messages.lorem_ipsum'),
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ]
        ];

        $pillarWeightedSum = 0.0;
        $pillarTotalWeight = 0.0;

        foreach ($pillars as $pillar) {
            $pillarResult = $this->calculatePillarResult($userId, $pillar->id, $methodologyId);
            $pillarPercentage = $pillarResult['percentage'] ?? 0;
            $pillarWeight = (float)($pillar->pivot->weight ?? 0.0);
            
            if ($pillarResult) {
                // Add to weighted calculation
                $pillarWeightedSum += ($pillarPercentage * $pillarWeight);
                $pillarTotalWeight += $pillarWeight;
            }
            
            $result['pillars'][] = [
                'id' => $pillar->id,
                'name' => $pillar->name,
                'description' => $pillar->description,
                'definition' => $pillar->definition,
                'objectives' => $pillar->objectives,
                'percentage' => $pillarPercentage,
                'weight' => $pillarWeight,
                'summary' => $pillarResult['summary'] ?? [
                    'text' => __('messages.lorem_ipsum'),
                    'total_questions' => 0,
                    'answered_questions' => 0,
                    'completion_rate' => 0,
                ],
            ];
        }

        // Calculate weighted average instead of simple average
        if ($pillarTotalWeight > 0.0) {
            $result['summary']['overall_percentage'] = round($pillarWeightedSum / $pillarTotalWeight, 2);
        }

        // Derive totals from pillar summaries
        $result['summary']['total_questions'] = array_sum(array_map(function ($p) {
            return (int)($p['summary']['total_questions'] ?? 0);
        }, $result['pillars']));
        $result['summary']['answered_questions'] = array_sum(array_map(function ($p) {
            return (int)($p['summary']['answered_questions'] ?? 0);
        }, $result['pillars']));

        $result['percentage'] = $result['summary']['overall_percentage'];
        return $result;
    }

    /**
     * Calculate pillar results: average of its modules if all completed; otherwise fallback to methodology questions assigned to pillar.
     */
    public function calculatePillarResult(int $userId, int $pillarId, int $methodologyId)
    {
        $pillar = Pillar::with(['modules'])->find($pillarId);
        if (!$pillar) {
            return null;
        }

        $allModulesCompleted = $this->areAllPillarModulesCompleted($userId, $methodologyId, $pillarId);
        if(!$allModulesCompleted) {
            return null;
        }

        $modules = $pillar->modulesForMethodology($methodologyId)->get();

        $result = [
            'modules' => [],
            'summary' => [
                'text' => __('messages.lorem_ipsum'),
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ]
        ];

        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $totalQuestions = 0;
        $answeredQuestions = 0;

        foreach ($modules as $module) {
            $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId, $pillarId);
            $modulePercentage = $moduleResult['percentage'] ?? 0;
            $moduleWeight = (float)($module->pivot->weight ?? 0.0);
            
            if ($moduleResult) {
                // Add to weighted calculation
                $weightedSum += ($modulePercentage * $moduleWeight);
                $totalWeight += $moduleWeight;
                $totalQuestions += (int)($moduleResult['summary']['total_questions'] ?? 0);
                $answeredQuestions += (int)($moduleResult['summary']['answered_questions'] ?? 0);
            }

            $result['modules'][] = [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'definition' => $module->definition,
                'objectives' => $module->objectives,
                'percentage' => $modulePercentage,
                'weight' => $moduleWeight,
                'summary' => $moduleResult['summary'] ?? [
                    'text' => __('messages.lorem_ipsum'),
                    'total_questions' => 0,
                    'answered_questions' => 0,
                    'completion_rate' => 0,
                ],
            ];
        }

        // Calculate weighted average instead of simple average
        if ($totalWeight > 0.0) {
            $result['summary']['overall_percentage'] = round($weightedSum / $totalWeight, 2);
        }

        $result['summary']['total_questions'] = $totalQuestions;
        $result['summary']['answered_questions'] = $answeredQuestions;
        $result['percentage'] = $result['summary']['overall_percentage'];
        return $result;
    }

    /**
     * Calculate module results based on its mode (simple/dynamic) in methodology_module or pillar_module.
     * Returns null when dynamic and not completed.
     */
    public function calculateModuleResult(int $userId, int $moduleId, int $methodologyId, ?int $pillarId = null)
    {
        $module = Module::find($moduleId);
        if (!$module) {
            return null;
        }

        $mode = $this->getModuleQuestionsMode($methodologyId, $moduleId, $pillarId);
        $contextCompleted = $this->isModuleContextCompleted($userId, $moduleId, $methodologyId, $pillarId);

        // Gather counts for summary
        $totalQuestions = (int) DB::table('module_question as mq')
            ->where('mq.methodology_id', $methodologyId)
            ->where('mq.module_id', $moduleId)
            ->when($pillarId !== null, function ($q) use ($pillarId) {
                $q->where('mq.pillar_id', $pillarId);
            }, function ($q) {
                $q->whereNull('mq.pillar_id');
            })
            ->count();

        if ($totalQuestions === 0) {
            return null;
        }

        $answeredQuestions = (int) DB::table('module_question as mq')
            ->join('user_answers as ua', function ($join) use ($userId, $moduleId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->where('ua.context_type', 'module')
                    ->where('ua.context_id', $moduleId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->where('mq.module_id', $moduleId)
            ->when($pillarId !== null, function ($q) use ($pillarId) {
                $q->where('mq.pillar_id', $pillarId);
            }, function ($q) {
                $q->whereNull('mq.pillar_id');
            })
            ->distinct()
            ->count('mq.question_id');

        if ($mode === 'dynamic' && !$contextCompleted) {
            return null;
        }

        $calc = $this->computePercentageForModule($userId, $methodologyId, $moduleId, $pillarId, $mode === 'simple');

        return [
            'percentage' => $calc['percentage'],
            'summary' => [
                'text' => __('messages.lorem_ipsum'),
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'completion_rate' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0.0,
            ]
        ];
    }

    /**
     * Simple denominator: sum of all methodology_question weights.
     */
    protected function computeSimplePercentageForMethodology(int $userId, int $methodologyId): array
    {
        $totals = DB::table('methodology_question as mq')
            ->selectRaw('COUNT(*) as total_questions, COALESCE(SUM(mq.weight), 0) as sum_weights')
            ->where('mq.methodology_id', $methodologyId)
            ->first();

        $answered = (int) DB::table('methodology_question as mq')
            ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->where('ua.context_type', 'methodology')
                    ->where('ua.context_id', $methodologyId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->distinct()
            ->count('mq.question_id');

        $numerator = DB::table('methodology_question as mq')
            ->leftJoin('answer_contexts as ac', function ($join) {
                $join->on('ac.context_id', '=', 'mq.id')
                    ->where('ac.context_type', 'methodology_question');
            })
            ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->on('ua.answer_id', '=', 'ac.answer_id')
                    ->where('ua.context_type', 'methodology')
                    ->where('ua.context_id', $methodologyId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->selectRaw('COALESCE(SUM(LEAST(GREATEST(ac.weight, 0), 100) * mq.weight), 0) as num')
            ->value('num');

        $sumWeights = (float) ($totals->sum_weights ?? 0);
        $percentage = $sumWeights > 0 ? round(((float)$numerator) / $sumWeights, 2) : 0.0;

        return [
            'percentage' => $percentage,
            'total_questions' => (int) ($totals->total_questions ?? 0),
            'answered_questions' => $answered,
        ];
    }

    /**
     * Fallback for simple methodology: compute dynamic-like percentage for methodology questions assigned to a specific item
     * (item can be a module or a pillar depending on $itemType).
     */
    protected function computeDynamicLikePercentageForMethodologyItem(int $userId, int $methodologyId, string $itemType, int $itemId)
    {
        // item_id on methodology_question points to module_id or pillar_id depending on assignment
        $itemColumnFilter = function ($q) use ($itemId) {
            $q->where('mq.item_id', $itemId);
        };

        $totals = DB::table('methodology_question as mq')
            ->selectRaw('COUNT(*) as total_questions')
            ->where('mq.methodology_id', $methodologyId)
            ->where($itemColumnFilter)
            ->first();

        $answered = (int)DB::table('methodology_question as mq')
            ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->where('ua.context_type', 'methodology')
                    ->where('ua.context_id', $methodologyId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->where($itemColumnFilter)
            ->distinct()
            ->count('mq.question_id');

        // Numerator: sum(answer_percent * weight)
        $numerator = DB::table('methodology_question as mq')
            ->leftJoin('answer_contexts as ac', function ($join) {
                $join->on('ac.context_id', '=', 'mq.id')
                    ->where('ac.context_type', 'methodology_question');
            })
            ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->on('ua.answer_id', '=', 'ac.answer_id')
                    ->where('ua.context_type', 'methodology')
                    ->where('ua.context_id', $methodologyId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->where($itemColumnFilter)
            ->selectRaw('COALESCE(SUM(LEAST(GREATEST(ac.weight, 0), 100) * mq.weight), 0) as num')
            ->value('num');

        // Denominator: sum of weights of answered questions only (dynamic-like)
        $denominator = DB::table('methodology_question as mq')
            ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->where('ua.context_type', 'methodology')
                    ->where('ua.context_id', $methodologyId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->where($itemColumnFilter)
            ->distinct()
            ->sum('mq.weight');

        $percentage = $denominator > 0 ? round(((float)$numerator) / (float)$denominator, 2) : 0.0;

        if ($answered == 0) {
            return null;
        } else {
            return [
                'percentage' => $percentage,
                'summary' => [
                    'text' => __('messages.lorem_ipsum'),
                    'total_questions' => (int)($totals->total_questions ?? 0),
                    'answered_questions' => $answered,
                    'completion_rate' => ($totals && $totals->total_questions > 0)
                        ? round(($answered / (int)$totals->total_questions) * 100, 2)
                        : 0.0,
                ]
            ];
        }
    }

    /**
     * Compute module percentage for simple or dynamic denominator.
     */
    protected function computePercentageForModule(int $userId, int $methodologyId, int $moduleId, ?int $pillarId, bool $simpleDenominator): array
    {
        // Numerator: sum(answer_percent * weight)
        $numerator = DB::table('module_question as mq')
            ->leftJoin('answer_contexts as ac', function ($join) {
                $join->on('ac.context_id', '=', 'mq.id')
                    ->where('ac.context_type', 'module_question');
            })
            ->join('user_answers as ua', function ($join) use ($userId, $moduleId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->on('ua.answer_id', '=', 'ac.answer_id')
                    ->where('ua.context_type', 'module')
                    ->where('ua.context_id', $moduleId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->where('mq.module_id', $moduleId)
            ->when($pillarId !== null, function ($q) use ($pillarId) {
                $q->where('mq.pillar_id', $pillarId);
            }, function ($q) {
                $q->whereNull('mq.pillar_id');
            })
            ->selectRaw('COALESCE(SUM(LEAST(GREATEST(ac.weight, 0), 100) * mq.weight), 0) as num')
            ->value('num');

        if ($simpleDenominator) {
            $denominator = (float) DB::table('module_question as mq')
                ->where('mq.methodology_id', $methodologyId)
                ->where('mq.module_id', $moduleId)
                ->when($pillarId !== null, function ($q) use ($pillarId) {
                    $q->where('mq.pillar_id', $pillarId);
                }, function ($q) {
                    $q->whereNull('mq.pillar_id');
                })
                ->sum('mq.weight');
        } else {
            // dynamic denominator: only answered questions' weights
            $denominator = (float) DB::table('module_question as mq')
                ->join('user_answers as ua', function ($join) use ($userId, $moduleId) {
                    $join->on('ua.question_id', '=', 'mq.question_id')
                        ->where('ua.context_type', 'module')
                        ->where('ua.context_id', $moduleId)
                        ->where('ua.user_id', $userId);
                })
                ->where('mq.methodology_id', $methodologyId)
                ->where('mq.module_id', $moduleId)
                ->when($pillarId !== null, function ($q) use ($pillarId) {
                    $q->where('mq.pillar_id', $pillarId);
                }, function ($q) {
                    $q->whereNull('mq.pillar_id');
                })
                ->distinct()
                ->sum('mq.weight');
        }

        $percentage = $denominator > 0 ? round(((float)$numerator) / $denominator, 2) : 0.0;
        return ['percentage' => $percentage];
    }

    /**
     * Detect a module's questions_mode from the appropriate pivot.
     */
    protected function getModuleQuestionsMode(int $methodologyId, int $moduleId, ?int $pillarId): string
    {
        if ($pillarId !== null) {
            $mode = DB::table('pillar_module')
                ->where('methodology_id', $methodologyId)
                ->where('pillar_id', $pillarId)
                ->where('module_id', $moduleId)
                ->value('questions_mode');
        } else {
            $mode = DB::table('methodology_module')
                ->where('methodology_id', $methodologyId)
                ->where('module_id', $moduleId)
                ->value('questions_mode');
        }

        return in_array($mode, ['simple', 'dynamic'], true) ? $mode : 'simple';
    }

    protected function isModuleContextCompleted(int $userId, int $moduleId, int $methodologyId, ?int $pillarId): bool
    {
        return $this->contextStatusService->getModuleStatus($userId, $moduleId, $methodologyId, $pillarId) === 'completed';
    }

    protected function areAllPillarModulesCompleted(int $userId, int $methodologyId, int $pillarId): bool
    {
        // Delegate to pillar-level status
        return $this->contextStatusService->getPillarStatus($userId, $pillarId, $methodologyId) === 'completed';
    }
}
