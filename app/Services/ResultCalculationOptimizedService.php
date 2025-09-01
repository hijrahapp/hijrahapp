<?php

namespace App\Services;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use Illuminate\Support\Facades\DB;

class ResultCalculationOptimizedService
{
    public function __construct(
        protected ?ContextStatusService $contextStatusService = null,
    ) {
        $this->contextStatusService = $contextStatusService ?? new ContextStatusService;
    }

    /**
     * Calculate methodology results honoring simple/dynamic modes.
     * Keeps the same return structure as ResultCalculationService.
     */
    public function calculateMethodologyResult(int $userId, int $methodologyId, ?bool $computeActualPercentage = false)
    {
        $methodology = Methodology::with(['pillars', 'modules'])->find($methodologyId);
        if (! $methodology) {
            return null;
        }

        $result = [
            'pillars' => [],
            'modules' => [],
            'summary' => [
                'text' => $methodology->report ?? '',
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ],
        ];

        // Methodology-level questions are always simple.
        $overall = $this->computeSimplePercentageForMethodology($userId, $methodologyId);
        if ($overall['answered_questions'] === 0) {
            return null;
        }

        $result['summary']['overall_percentage'] = $overall['percentage'];
        $result['summary']['total_questions'] = $overall['total_questions'];
        $result['summary']['answered_questions'] = $overall['answered_questions'];

        if ($methodology->type === 'complex' || $methodology->type === 'twoSection') {
            $isTwoSection = ($methodology->type === 'twoSection');
            $pillars = ($isTwoSection && !$computeActualPercentage)
                ? $methodology->pillars()->wherePivot('section', 'first')->get()
                : $methodology->pillars;

            $pillarWeightedSum = 0.0;
            $pillarTotalWeight = 0.0;

            foreach ($pillars as $pillar) {

                if ($computeActualPercentage) {
                    $pillarResult = $this->calculatePillarResult($userId, $pillar->id, $methodologyId);
                } else {
                    $pillarResult = $this->computeDynamicLikePercentageForMethodologyItem($userId, $methodologyId, 'pillar', $pillar->id);
                }

                $pillarPercentage = $pillarResult['percentage'] ?? 0;
                $pillarWeight = (float) ($pillar->pivot->weight ?? 0.0);

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
                        'text' => $pillar->pivot->report ?? '',
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

                if ($computeActualPercentage) {
                    $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId, null);
                } else {
                    $moduleResult = $this->computeDynamicLikePercentageForMethodologyItem($userId, $methodologyId, 'module', $module->id);
                }

                $modulePercentage = $moduleResult['percentage'] ?? 0;
                $moduleWeight = (float) ($module->pivot->weight ?? 0.0);

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
                        'text' => $module->pivot->report ?? '',
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
        if (! $methodology || $methodology->type !== 'twoSection') {
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
                'text' => $methodology->report ?? '',
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ],
        ];

        $pillarWeightedSum = 0.0;
        $pillarTotalWeight = 0.0;

        foreach ($pillars as $pillar) {
            $pillarResult = $this->calculatePillarResult($userId, $pillar->id, $methodologyId);
            $pillarPercentage = $pillarResult['percentage'] ?? 0;
            $pillarWeight = (float) ($pillar->pivot->weight ?? 0.0);

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
                    'text' => $pillar->pivot->report ?? '',
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
            return (int) ($p['summary']['total_questions'] ?? 0);
        }, $result['pillars']));
        $result['summary']['answered_questions'] = array_sum(array_map(function ($p) {
            return (int) ($p['summary']['answered_questions'] ?? 0);
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
        if (! $pillar) {
            return null;
        }

        $allModulesCompleted = $this->areAllPillarModulesCompleted($userId, $methodologyId, $pillarId);
        if (! $allModulesCompleted) {
            return null;
        }

        // Get pillar report from methodology_pillar pivot
        $pillarPivot = DB::table('methodology_pillar')
            ->where('methodology_id', $methodologyId)
            ->where('pillar_id', $pillarId)
            ->first();

        $modules = $pillar->modulesForMethodology($methodologyId)->get();

        $result = [
            'modules' => [],
            'summary' => [
                'text' => $pillarPivot->report ?? '',
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ],
        ];

        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $totalQuestions = 0;
        $answeredQuestions = 0;

        foreach ($modules as $module) {
            $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId, $pillarId);
            $modulePercentage = $moduleResult['percentage'] ?? 0;
            $moduleWeight = (float) ($module->pivot->weight ?? 0.0);

            if ($moduleResult) {
                // Add to weighted calculation
                $weightedSum += ($modulePercentage * $moduleWeight);
                $totalWeight += $moduleWeight;
                $totalQuestions += (int) ($moduleResult['summary']['total_questions'] ?? 0);
                $answeredQuestions += (int) ($moduleResult['summary']['answered_questions'] ?? 0);
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
                    'text' => $module->pivot->report ?? '',
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
        if (! $module) {
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

        if ($mode === 'dynamic' && ! $contextCompleted) {
            return null;
        }

        // Get the appropriate report field based on context
        $reportText = __('messages.lorem_ipsum');
        if ($pillarId !== null) {
            // Get report from pillar_module pivot
            $pivotData = DB::table('pillar_module')
                ->where('methodology_id', $methodologyId)
                ->where('pillar_id', $pillarId)
                ->where('module_id', $moduleId)
                ->first();
            $reportText = $pivotData->report ?? '';
        } else {
            // Get report from methodology_module pivot
            $pivotData = DB::table('methodology_module')
                ->where('methodology_id', $methodologyId)
                ->where('module_id', $moduleId)
                ->first();
            $reportText = $pivotData->report ?? '';
        }

        $calc = $this->computePercentageForModule($userId, $methodologyId, $moduleId, $pillarId, $mode === 'simple');

        return [
            'percentage' => $calc['percentage'],
            'summary' => [
                'text' => $reportText,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'completion_rate' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0.0,
            ],
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

        // Group multiple selections per question and normalize MCQMultiple
        $numerator = $this->computeWeightedSumForContext(
            $userId,
            $methodologyId,
            'methodology_question',
            'methodology',
            $methodologyId
        );

        $sumWeights = (float) ($totals->sum_weights ?? 0);
        $percentage = $sumWeights > 0 ? round(((float) $numerator) / $sumWeights, 2) : 0.0;

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
            ->selectRaw('COUNT(*) as total_questions, COALESCE(SUM(mq.weight), 0) as sum_weights')
            ->where('mq.methodology_id', $methodologyId)
            ->where($itemColumnFilter)
            ->first();

        $answered = (int) DB::table('methodology_question as mq')
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

        // Numerator: group and normalize MCQMultiple for this item context
        $numerator = $this->computeWeightedSumForContextWithFilter(
            $userId,
            $methodologyId,
            'methodology_question',
            'methodology',
            $methodologyId,
            $itemColumnFilter
        );

        $sumWeights = (float) ($totals->sum_weights ?? 0);
        $percentage = $sumWeights > 0 ? round(((float) $numerator) / (float) $sumWeights, 2) : 0.0;

        if ($answered == 0) {
            return null;
        } else {
            // Get methodology report for fallback methodology item calculations
            $methodology = Methodology::find($methodologyId);
            $reportText = $methodology->report ?? '';

            return [
                'percentage' => $percentage,
                'summary' => [
                    'text' => $reportText,
                    'total_questions' => (int) ($totals->total_questions ?? 0),
                    'answered_questions' => $answered,
                    'completion_rate' => ($totals && $totals->total_questions > 0)
                        ? round(($answered / (int) $totals->total_questions) * 100, 2)
                        : 0.0,
                ],
            ];
        }
    }

    /**
     * Compute module percentage for simple or dynamic denominator.
     */
    protected function computePercentageForModule(int $userId, int $methodologyId, int $moduleId, ?int $pillarId, bool $simpleDenominator): array
    {
        // Numerator: group multiple selections per question and normalize MCQMultiple in module context
        $numerator = $this->computeWeightedSumForModuleContext(
            $userId,
            $methodologyId,
            $moduleId,
            $pillarId,
            'module_question',
            'module',
            $moduleId
        );

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

        $percentage = $denominator > 0 ? round(((float) $numerator) / $denominator, 2) : 0.0;

        return ['percentage' => $percentage];
    }

    /**
     * Group answers by question and normalize MCQMultiple using weighted sum.
     * QuestionScore = (Σ(selectedAnswerWeights) / NumberOfPossibleAnswers) * questionWeight
     */
    protected function computeWeightedSumForContext(int $userId, int $methodologyId, string $contextType, string $userContextType, int $userContextId): float
    {
        return $this->computeWeightedSumForContextWithFilter($userId, $methodologyId, $contextType, $userContextType, $userContextId, null);
    }

    protected function computeWeightedSumForContextWithFilter(
        int $userId,
        int $methodologyId,
        string $contextType,
        string $userContextType,
        int $userContextId,
        ?\Closure $additionalFilter
    ): float {
        $rows = DB::table('methodology_question as mq')
            ->leftJoin('questions as q', 'q.id', '=', 'mq.question_id')
            ->leftJoin('answer_contexts as ac', function ($join) use ($contextType) {
                $join->on('ac.context_id', '=', 'mq.id')
                    ->where('ac.context_type', $contextType);
            })
            ->leftJoin('user_answers as ua', function ($join) use ($userId, $userContextType, $userContextId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->on('ua.answer_id', '=', 'ac.answer_id')
                    ->where('ua.context_type', $userContextType)
                    ->where('ua.context_id', $userContextId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->when($additionalFilter, $additionalFilter)
            ->whereNotNull('ua.id')
            ->select([
                'mq.question_id',
                'q.type as question_type',
                'mq.id as mq_id',
                'mq.weight as question_weight',
                'ac.answer_id',
                'ac.weight as answer_weight',
            ])
            ->get();

        $byQuestion = [];
        foreach ($rows as $r) {
            $qid = (int) $r->question_id;
            $qType = (string) $r->question_type;
            $qWeight = (float) $r->question_weight;
            $aWeight = max(0.0, min(100.0, (float) ($r->answer_weight ?? 0)));

            if (! isset($byQuestion[$qid])) {
                $byQuestion[$qid] = [
                    'type' => $qType,
                    'weight' => $qWeight,
                    'mq_id' => (int) $r->mq_id,
                    'sum_selected' => 0.0,
                    'total_possible' => 0,
                ];
            }
            $byQuestion[$qid]['sum_selected'] += $aWeight;
        }

        foreach ($byQuestion as $qid => $q) {
            if ($q['type'] === 'MCQMultiple') {
                $total = DB::table('answer_contexts as ac')
                    ->where('ac.context_type', $contextType)
                    ->where('ac.context_id', $q['mq_id'])
                    ->distinct()
                    ->count('ac.answer_id');
                $byQuestion[$qid]['total_possible'] = (int) $total;
            }
        }

        $totalSum = 0.0;
        foreach ($byQuestion as $q) {
            if ($q['type'] === 'MCQMultiple' && $q['total_possible'] > 0) {
                $totalSum += ($q['sum_selected'] / $q['total_possible']) * $q['weight'];
            } else {
                $totalSum += $q['sum_selected'] * $q['weight'];
            }
        }

        return $totalSum;
    }

    protected function computeWeightedSumForModuleContext(
        int $userId,
        int $methodologyId,
        int $moduleId,
        ?int $pillarId,
        string $contextType,
        string $userContextType,
        int $userContextId
    ): float {
        $rows = DB::table('module_question as mq')
            ->leftJoin('questions as q', 'q.id', '=', 'mq.question_id')
            ->leftJoin('answer_contexts as ac', function ($join) use ($contextType) {
                $join->on('ac.context_id', '=', 'mq.id')
                    ->where('ac.context_type', $contextType);
            })
            ->leftJoin('user_answers as ua', function ($join) use ($userId, $userContextType, $userContextId) {
                $join->on('ua.question_id', '=', 'mq.question_id')
                    ->on('ua.answer_id', '=', 'ac.answer_id')
                    ->where('ua.context_type', $userContextType)
                    ->where('ua.context_id', $userContextId)
                    ->where('ua.user_id', $userId);
            })
            ->where('mq.methodology_id', $methodologyId)
            ->where('mq.module_id', $moduleId)
            ->when($pillarId !== null, function ($q) use ($pillarId) {
                $q->where('mq.pillar_id', $pillarId);
            }, function ($q) {
                $q->whereNull('mq.pillar_id');
            })
            ->whereNotNull('ua.id')
            ->select([
                'mq.question_id',
                'q.type as question_type',
                'mq.id as mq_id',
                'mq.weight as question_weight',
                'ac.answer_id',
                'ac.weight as answer_weight',
            ])
            ->get();

        $byQuestion = [];
        foreach ($rows as $r) {
            $qid = (int) $r->question_id;
            $qType = (string) $r->question_type;
            $qWeight = (float) $r->question_weight;
            $aWeight = max(0.0, min(100.0, (float) ($r->answer_weight ?? 0)));

            if (! isset($byQuestion[$qid])) {
                $byQuestion[$qid] = [
                    'type' => $qType,
                    'weight' => $qWeight,
                    'mq_id' => (int) $r->mq_id,
                    'sum_selected' => 0.0,
                    'total_possible' => 0,
                ];
            }
            $byQuestion[$qid]['sum_selected'] += $aWeight;
        }

        foreach ($byQuestion as $qid => $q) {
            if ($q['type'] === 'MCQMultiple') {
                $total = DB::table('answer_contexts as ac')
                    ->where('ac.context_type', $contextType)
                    ->where('ac.context_id', $q['mq_id'])
                    ->distinct()
                    ->count('ac.answer_id');
                $byQuestion[$qid]['total_possible'] = (int) $total;
            }
        }

        $totalSum = 0.0;
        foreach ($byQuestion as $q) {
            if ($q['type'] === 'MCQMultiple' && $q['total_possible'] > 0) {
                $totalSum += ($q['sum_selected'] / $q['total_possible']) * $q['weight'];
            } else {
                $totalSum += $q['sum_selected'] * $q['weight'];
            }
        }

        return $totalSum;
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
