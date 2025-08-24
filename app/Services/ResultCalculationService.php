<?php

namespace App\Services;

use App\Models\UserAnswer;
use App\Models\UserContextStatus;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

class ResultCalculationService
{
    /**
     * Calculate methodology results based on user answers
     */
    public function calculateMethodologyResult(int $userId, int $methodologyId)
    {
        $methodology = Methodology::with(['pillars', 'modules'])->find($methodologyId);

        if (!$methodology) {
            return null;
        }

        // Get total questions and answered questions
        $totalQuestions = $methodology->questions()->count();
        $answeredQuestions = UserAnswer::where('user_id', $userId)
            ->where('context_type', 'methodology')
            ->where('context_id', $methodologyId)
            ->distinct('question_id')
            ->count('question_id');

        if ($answeredQuestions != $totalQuestions) {
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

        // Calculate pillar results (complex/twoSection) or module results (simple)
        if($methodology->type == 'complex'|| $methodology->type == 'twoSection') {
            // Complex/twoSection: methodology questions are assigned to pillars via item_id
            $isTwoSection = ($methodology->type === 'twoSection');
            $pillars = $isTwoSection
                ? $methodology->pillars()->wherePivot('section', 'first')->get()
                : $methodology->pillars;

            $pillarScores = [];
            $pillarNumerators = [];
            $pillarTotals = [];
            foreach ($pillars as $pillar) {
                $pillarScores[$pillar->id] = 0.0;
                $pillarNumerators[$pillar->id] = 0.0;
                $pillarTotals[$pillar->id] = [
                    'total_questions' => 0,
                    'answered_questions' => 0,
                ];
            }

            // Aggregate per-question selected answer weights via a single SQL query
            $aggRows = DB::table('methodology_question as mq')
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
                ->when($isTwoSection, function ($q) use ($methodologyId) {
                    $q->join('methodology_pillar as mp', function ($join) use ($methodologyId) {
                        $join->on('mp.pillar_id', '=', 'mq.item_id')
                            ->where('mp.methodology_id', $methodologyId)
                            ->where('mp.section', 'first');
                    });
                })
                ->where('mq.methodology_id', $methodologyId)
                ->groupBy('mq.id', 'mq.question_id', 'mq.weight', 'mq.item_id')
                ->get([
                    'mq.id',
                    'mq.question_id',
                    'mq.weight',
                    'mq.item_id',
                    DB::raw('COALESCE(SUM(ac.weight), 0) as answer_percent')
                ]);

            if ($aggRows->isNotEmpty()) {
                // Preload all question weights to compute per-pillar normalized weights (push into 100 per pillar)
                $weightRows = DB::table('methodology_question as mqw')
                    ->when($isTwoSection, function ($q) use ($methodologyId) {
                        $q->join('methodology_pillar as mp', function ($join) use ($methodologyId) {
                            $join->on('mp.pillar_id', '=', 'mqw.item_id')
                                ->where('mp.methodology_id', $methodologyId)
                                ->where('mp.section', 'first');
                        });
                    })
                    ->where('mqw.methodology_id', $methodologyId)
                    ->get(['mqw.question_id', 'mqw.item_id', 'mqw.weight']);
                $questionGlobalWeight = [];
                $questionPillarId = [];
                $pillarSumWeight = [];
                foreach ($weightRows as $wr) {
                    $qid = (int)$wr->question_id;
                    $pid = $wr->item_id !== null ? (int)$wr->item_id : null;
                    $w = (float)$wr->weight;
                    $questionGlobalWeight[$qid] = $w;
                    $questionPillarId[$qid] = $pid;
                    if ($pid !== null) {
                        $pillarSumWeight[$pid] = ($pillarSumWeight[$pid] ?? 0.0) + $w;
                    }
                }

                $overallNumerator = 0.0;
                foreach ($aggRows as $row) {
                    $questionId = (int)$row->question_id;
                    $questionWeight = $questionGlobalWeight[$questionId] ?? (float)$row->weight;
                    $pillarId = $questionPillarId[$questionId] ?? ($row->item_id !== null ? (int)$row->item_id : null);

                    $answerPercent = (float)$row->answer_percent;
                    if ($answerPercent < 0) $answerPercent = 0.0;
                    if ($answerPercent > 100) $answerPercent = 100.0;

                    // Numerators for overall and pillar scores (denominator is sum of weights)
                    $overallNumerator += ($questionWeight * $answerPercent);

                    if ($pillarId !== null && array_key_exists($pillarId, $pillarNumerators)) {
                        $pillarNumerators[$pillarId] += ($questionWeight * $answerPercent);
                    }
                }

                // Compute total and answered counts per pillar independent of scores
                $pillarQuestionCounts = DB::table('methodology_question as mq')
                    ->when($isTwoSection, function ($q) use ($methodologyId) {
                        $q->join('methodology_pillar as mp', function ($join) use ($methodologyId) {
                            $join->on('mp.pillar_id', '=', 'mq.item_id')
                                ->where('mp.methodology_id', $methodologyId)
                                ->where('mp.section', 'first');
                        });
                    })
                    ->selectRaw('mq.item_id as item_id, COUNT(*) as total_questions')
                    ->where('mq.methodology_id', $methodologyId)
                    ->groupBy('mq.item_id')
                    ->pluck('total_questions', 'item_id');

                $pillarAnsweredCounts = DB::table('methodology_question as mq')
                    ->when($isTwoSection, function ($q) use ($methodologyId) {
                        $q->join('methodology_pillar as mp', function ($join) use ($methodologyId) {
                            $join->on('mp.pillar_id', '=', 'mq.item_id')
                                ->where('mp.methodology_id', $methodologyId)
                                ->where('mp.section', 'first');
                        });
                    })
                    ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                        $join->on('ua.question_id', '=', 'mq.question_id')
                            ->where('ua.context_type', 'methodology')
                            ->where('ua.context_id', $methodologyId)
                            ->where('ua.user_id', $userId);
                    })
                    ->selectRaw('mq.item_id as item_id, COUNT(DISTINCT mq.question_id) as answered_questions')
                    ->where('mq.methodology_id', $methodologyId)
                    ->groupBy('mq.item_id')
                    ->pluck('answered_questions', 'item_id');

                // Build pillars list with computed percentages
                foreach ($pillars as $pillar) {
                    $denom = (float)($pillarSumWeight[$pillar->id] ?? 0.0);
                    $pillarScore = $denom > 0.0 ? ($pillarNumerators[$pillar->id] / $denom) : 0.0;
                    $score = round($pillarScore, 2);
                    $totals = [
                        'total_questions' => (int)($pillarQuestionCounts[$pillar->id] ?? 0),
                        'answered_questions' => (int)($pillarAnsweredCounts[$pillar->id] ?? 0),
                    ];
                    $completionRate = $totals['total_questions'] > 0
                        ? round(($totals['answered_questions'] / $totals['total_questions']) * 100, 2)
                        : 0.0;
                    $result['pillars'][] = [
                        'id' => $pillar->id,
                        'name' => $pillar->name,
                        'description' => $pillar->description,
                        'definition' => $pillar->definition,
                        'objectives' => $pillar->objectives,
                        'percentage' => $score,
                        'summary' => [
                            'text' => __('messages.lorem_ipsum'),
                            'total_questions' => $totals['total_questions'],
                            'answered_questions' => $totals['answered_questions'],
                            'completion_rate' => $completionRate,
                        ],
                    ];
                }

                // Overall score normalized by total weight across all methodology questions in scope
                $totalWeight = 0.0;
                foreach ($pillarSumWeight as $w) { $totalWeight += (float)$w; }
                $overallScore = $totalWeight > 0.0 ? ($overallNumerator / $totalWeight) : 0.0;
                $result['summary']['overall_percentage'] = round($overallScore, 2);
            }
        } else {
            // Simple methodology: questions are attached at methodology level with weights (methodology_question)
            // and each question is linked to a module via item_id. Compute module scores by grouping.
            $moduleScores = [];
            $moduleNumerators = [];
            $moduleTotals = [];
            foreach ($methodology->modules as $module) {
                $moduleScores[$module->id] = 0.0;
                $moduleNumerators[$module->id] = 0.0;
                $moduleTotals[$module->id] = [
                    'total_questions' => 0,
                    'answered_questions' => 0,
                ];
            }

            // Aggregate per-question selected answer weights via a single SQL query
            $aggRows = DB::table('methodology_question as mq')
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
                ->groupBy('mq.id', 'mq.question_id', 'mq.weight', 'mq.item_id')
                ->get([
                    'mq.id',
                    'mq.question_id',
                    'mq.weight',
                    'mq.item_id',
                    DB::raw('COALESCE(SUM(ac.weight), 0) as answer_percent')
                ]);

            if ($aggRows->isNotEmpty()) {
                // Preload all question weights to compute per-module normalized weights (push into 100 per module)
                $weightRows = DB::table('methodology_question as mqw')
                    ->where('mqw.methodology_id', $methodologyId)
                    ->get(['mqw.question_id', 'mqw.item_id', 'mqw.weight']);
                $questionGlobalWeight = [];
                $questionModuleId = [];
                $moduleSumWeight = [];
                foreach ($weightRows as $wr) {
                    $qid = (int)$wr->question_id;
                    $mid = $wr->item_id !== null ? (int)$wr->item_id : null;
                    $w = (float)$wr->weight;
                    $questionGlobalWeight[$qid] = $w;
                    $questionModuleId[$qid] = $mid;
                    if ($mid !== null) {
                        $moduleSumWeight[$mid] = ($moduleSumWeight[$mid] ?? 0.0) + $w;
                    }
                }

                $overallNumerator = 0.0;
                foreach ($aggRows as $row) {
                    $questionId = (int)$row->question_id;
                    $questionWeight = $questionGlobalWeight[$questionId] ?? (float)$row->weight;
                    $moduleId = $questionModuleId[$questionId] ?? ($row->item_id !== null ? (int)$row->item_id : null);

                    $answerPercent = (float)$row->answer_percent;
                    if ($answerPercent < 0) $answerPercent = 0.0;
                    if ($answerPercent > 100) $answerPercent = 100.0;

                    // Numerators for overall and per-module scores (denominator is sum of weights)
                    $overallNumerator += ($questionWeight * $answerPercent);

                    if ($moduleId !== null && array_key_exists($moduleId, $moduleNumerators)) {
                        $moduleNumerators[$moduleId] += ($questionWeight * $answerPercent);
                    }
                }

                // Compute total and answered counts per module independent of scores
                $moduleQuestionCounts = DB::table('methodology_question as mq')
                    ->selectRaw('mq.item_id as item_id, COUNT(*) as total_questions')
                    ->where('mq.methodology_id', $methodologyId)
                    ->groupBy('mq.item_id')
                    ->pluck('total_questions', 'item_id');

                $moduleAnsweredCounts = DB::table('methodology_question as mq')
                    ->join('user_answers as ua', function ($join) use ($userId, $methodologyId) {
                        $join->on('ua.question_id', '=', 'mq.question_id')
                            ->where('ua.context_type', 'methodology')
                            ->where('ua.context_id', $methodologyId)
                            ->where('ua.user_id', $userId);
                    })
                    ->selectRaw('mq.item_id as item_id, COUNT(DISTINCT mq.question_id) as answered_questions')
                    ->where('mq.methodology_id', $methodologyId)
                    ->groupBy('mq.item_id')
                    ->pluck('answered_questions', 'item_id');

                // Build modules list with computed percentages
                foreach ($methodology->modules as $module) {
                    $denom = (float)($moduleSumWeight[$module->id] ?? 0.0);
                    $moduleScore = $denom > 0.0 ? ($moduleNumerators[$module->id] / $denom) : 0.0;
                    $score = round($moduleScore, 2);
                    $totals = [
                        'total_questions' => (int)($moduleQuestionCounts[$module->id] ?? 0),
                        'answered_questions' => (int)($moduleAnsweredCounts[$module->id] ?? 0),
                    ];
                    $completionRate = $totals['total_questions'] > 0
                        ? round(($totals['answered_questions'] / $totals['total_questions']) * 100, 2)
                        : 0.0;
                    $result['modules'][] = [
                        'id' => $module->id,
                        'name' => $module->name,
                        'description' => $module->description,
                        'definition' => $module->definition,
                        'objectives' => $module->objectives,
                        'percentage' => $score,
                        'summary' => [
                            'text' => __('messages.lorem_ipsum'),
                            'total_questions' => $totals['total_questions'],
                            'answered_questions' => $totals['answered_questions'],
                            'completion_rate' => $completionRate,
                        ],
                    ];
                }

                // Overall score normalized by total weight across methodology questions
                $totalWeight = 0.0;
                foreach ($moduleSumWeight as $w) { $totalWeight += (float)$w; }
                $overallScore = $totalWeight > 0.0 ? ($overallNumerator / $totalWeight) : 0.0;
                $result['summary']['overall_percentage'] = round($overallScore, 2);
            }
        }

        // Calculate overall summary
        // For complex/twoSection methodologies, fallback to average if overall not already computed
        // if (($methodology->type === 'complex' || $methodology->type === 'twoSection')
        //     && ($result['summary']['overall_percentage'] === 0)) {
        //     $allPercentages = [];
        //     if (!empty($result['pillars'])) {
        //         $allPercentages = array_merge($allPercentages, array_column($result['pillars'], 'percentage'));
        //     }
        //     if (!empty($result['modules'])) {
        //         $allPercentages = array_merge($allPercentages, array_column($result['modules'], 'percentage'));
        //     }
        //     if (!empty($allPercentages)) {
        //         $result['summary']['overall_percentage'] = round(array_sum($allPercentages) / count($allPercentages), 2);
        //     }
        // }

        $result['summary']['total_questions'] = $totalQuestions;
        $result['summary']['answered_questions'] = $answeredQuestions;

        return $result;
    }

    /**
     * Calculate section results for a two-section methodology by aggregating pillar module results
     */
    public function calculateSectionResult(int $userId, int $methodologyId, int $sectionNumber)
    {
        $methodology = Methodology::with(['pillars'])->find($methodologyId);

        if (!$methodology || $methodology->type !== 'twoSection') {
            return null;
        }

        $section = $sectionNumber === 2 ? 'second' : 'first';

        $pillars = $methodology->pillars()
            ->wherePivot('section', $section)
            ->get();

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

        $pillarPercentages = [];
        $totalQuestions = 0;
        $answeredQuestions = 0;

        foreach ($pillars as $pillar) {
            try {
                $pillarResult = $this->calculatePillarResult($userId, $pillar->id, $methodologyId);
                $pillarPercentage = $pillarResult['percentage'] ?? 0;
                $pillarPercentages[] = $pillarPercentage;
                $totalQuestions += $pillarResult['summary']['total_questions'] ?? 0;
                $answeredQuestions += $pillarResult['summary']['answered_questions'] ?? 0;

                $result['pillars'][] = [
                    'id' => $pillar->id,
                    'name' => $pillar->name,
                    'description' => $pillar->description,
                    'definition' => $pillar->definition,
                    'objectives' => $pillar->objectives,
                    'percentage' => $pillarPercentage,
                    'summary' => $pillarResult['summary'] ?? []
                ];
            } catch (\Exception $e) {
                \Log::error('Error calculating pillar result in section: ' . $e->getMessage());
                $result['pillars'][] = [
                    'id' => $pillar->id,
                    'name' => $pillar->name,
                    'description' => $pillar->description,
                    'definition' => $pillar->definition,
                    'objectives' => $pillar->objectives,
                    'percentage' => 0,
                    'summary' => [
                        'text' => __('messages.lorem_ipsum'),
                        'total_questions' => 0,
                        'answered_questions' => 0,
                        'completion_rate' => 0,
                    ]
                ];
            }
        }

        if (!empty($pillarPercentages)) {
            $result['summary']['overall_percentage'] = round(array_sum($pillarPercentages) / count($pillarPercentages), 2);
        }

        $result['summary']['total_questions'] = $totalQuestions;
        $result['summary']['answered_questions'] = $answeredQuestions;

        // For convenience on the consumer side
        $result['percentage'] = $result['summary']['overall_percentage'];

        return $result;
    }

    /**
     * Calculate pillar results based on user answers
     */
    public function calculatePillarResult(int $userId, int $pillarId, int $methodologyId)
    {
        $pillar = Pillar::with(['modules'])->find($pillarId);

        if (!$pillar) {
            return null;
        }

        $result = [
            'modules' => [],
            'summary' => [
                'text' => __('messages.lorem_ipsum'),
                'overall_percentage' => 0,
                'total_questions' => 0,
                'answered_questions' => 0,
            ]
        ];

        // Get modules for this pillar in this specific methodology
        $modules = $pillar->modulesForMethodology($methodologyId)->get();

        // Calculate module results
        $pillarTotalQuestions = 0;
        $pillarAnsweredQuestions = 0;
        $modulePercentages = [];
        foreach ($modules as $module) {
            try {
                $moduleResult = $this->calculateModuleResult($userId, $module->id, $methodologyId, $pillarId);
                $modulePercentage = $moduleResult['percentage'] ?? 0;
                $modulePercentages[] = $modulePercentage;
                $pillarTotalQuestions += $moduleResult['summary']['total_questions'] ?? 0;
                $pillarAnsweredQuestions += $moduleResult['summary']['answered_questions'] ?? 0;
                $result['modules'][] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'description' => $module->description,
                    'definition' => $module->definition,
                    'objectives' => $module->objectives,
                    'percentage' => $modulePercentage,
                    'summary' => $moduleResult['summary'] ?? []
                ];
            } catch (\Exception $e) {
                // Log error and continue with default values
                \Log::error('Error calculating module result in pillar: ' . $e->getMessage());
                $result['modules'][] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'description' => $module->description,
                    'definition' => $module->definition,
                    'objectives' => $module->objectives,
                    'percentage' => 0,
                    'summary' => [
                        'text' => __('messages.lorem_ipsum'),
                        'total_questions' => 0,
                        'answered_questions' => 0,
                        'completion_rate' => 0,
                    ]
                ];
            }
        }

        // Calculate overall summary
        if (!empty($modulePercentages)) {
            $result['summary']['overall_percentage'] = round(array_sum($modulePercentages) / count($modulePercentages), 2);
        }

        $result['summary']['total_questions'] = $pillarTotalQuestions;
        $result['summary']['answered_questions'] = $pillarAnsweredQuestions;

        // Add percentage field for methodology calculation
        $result['percentage'] = $result['summary']['overall_percentage'];

        return $result;
    }

    /**
     * Calculate module results based on user answers
     */
    public function calculateModuleResult(int $userId, int $moduleId, int $methodologyId, ?int $pillarId = null)
    {
        $module = Module::find($moduleId);

        if (!$module) {
            return null;
        }

        // Total questions for this module in the specific methodology/pillar context
        $totalQuestionsQuery = DB::table('module_question as mq')
            ->where('mq.methodology_id', $methodologyId)
            ->where('mq.module_id', $moduleId);
        $totalQuestionsQuery = $pillarId !== null
            ? $totalQuestionsQuery->where('mq.pillar_id', $pillarId)
            : $totalQuestionsQuery->whereNull('mq.pillar_id');
        $totalQuestions = (int)$totalQuestionsQuery->count();

        if ($totalQuestions === 0) {
            return null;
        }

        // Aggregate selected answer weights per question within this module context
        $aggRows = DB::table('module_question as mq')
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
            ->groupBy('mq.id', 'mq.question_id', 'mq.weight')
            ->get([
                'mq.id',
                'mq.question_id',
                'mq.weight',
                DB::raw('COALESCE(SUM(ac.weight), 0) as answer_percent')
            ]);

        // Count answered questions (distinct) in this module context
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

        // Compute total weight for all questions in this module context
        $totalWeightQuery = DB::table('module_question as mq')
            ->where('mq.methodology_id', $methodologyId)
            ->where('mq.module_id', $moduleId);
        $totalWeightQuery = $pillarId !== null
            ? $totalWeightQuery->where('mq.pillar_id', $pillarId)
            : $totalWeightQuery->whereNull('mq.pillar_id');
        $totalWeight = (float)$totalWeightQuery->sum('mq.weight');

        // Compute weighted percentage normalized by total weight (unanswered contribute 0 in numerator)
        $overallNumerator = 0.0;
        foreach ($aggRows as $row) {
            $answerPercent = (float)$row->answer_percent;
            if ($answerPercent < 0) $answerPercent = 0.0;
            if ($answerPercent > 100) $answerPercent = 100.0;
            $overallNumerator += ((float)$row->weight * $answerPercent);
        }

        $percentage = $totalWeight > 0.0 ? round($overallNumerator / $totalWeight, 2) : 0.0;
        $completionRate = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0.0;

        return [
            'percentage' => $percentage,
            'summary' => [
                'text' => __('messages.lorem_ipsum'),
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'completion_rate' => $completionRate,
            ]
        ];
    }

    /**
     * Determine completion status for a pillar for a given user in a methodology
     * Returns: not_started | in_progress | completed
     */
    public function getPillarStatus(int $userId, int $pillarId, int $methodologyId): string
    {
        $pillar = Pillar::find($pillarId);
        if (!$pillar) {
            return 'not_started';
        }

        // Get all modules associated with this pillar in the given methodology
        $modules = $pillar->modulesForMethodology($methodologyId)->get();
        if ($modules->isEmpty()) {
            return 'not_started';
        }

        $moduleIds = $modules->pluck('id')->all();

        // Fetch statuses for all module contexts scoped by methodology and pillar
        $statuses = UserContextStatus::where('user_id', $userId)
            ->where('context_type', 'module')
            ->whereIn('context_id', $moduleIds)
            ->where('methodology_id', $methodologyId)
            ->where('pillar_id', $pillarId)
            ->pluck('status', 'context_id');

        if ($statuses->isEmpty()) {
            return 'not_started';
        }

        // If any module is in progress → pillar is in progress
        if ($statuses->contains('in_progress')) {
            return 'in_progress';
        }

        // If some modules are missing statuses or not all are completed → still in progress
        if ($statuses->count() < count($moduleIds)) {
            return 'in_progress';
        }

        // All module statuses are completed
        return 'completed';
    }

    /**
     * Determine completion status for a module for a given user in a methodology (and pillar when applicable)
     * Returns: not_started | in_progress | completed
     */
    public function getModuleStatus(int $userId, int $moduleId, int $methodologyId, ?int $pillarId = null): string
    {
        // Read status from user_context_statuses
        $statusQuery = UserContextStatus::where('user_id', $userId)
            ->where('context_type', 'module')
            ->where('context_id', $moduleId)
            ->where('methodology_id', $methodologyId);

        if ($pillarId) {
            $statusQuery->where('pillar_id', $pillarId);
        } else {
            $statusQuery->where('pillar_id', 0);
        }

        $status = $statusQuery->value('status');

        if (!$status) {
            return 'not_started';
        }

        return $status;
    }
}
