<?php

namespace App\Livewire\Homepage\Methodologies\Users;

use App\Enums\QuestionType;
use App\Models\Methodology;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\ContextStatusService;
use App\Services\ResultCalculationOptimizedService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class MethodologyUserDetails extends Component
{
    public Methodology $methodology;

    public User $user;

    public function mount(Methodology $methodology, User $user)
    {
        $this->methodology = $methodology;
        $this->user = $user;
    }

    #[Computed]
    public function generalQuestions()
    {
        // General questions are those attached directly to methodology (methodology_question)
        return $this->methodology->questions()->get();
    }

    #[Computed]
    public function generalQuestionTypes(): array
    {
        return $this->generalQuestions
            ->pluck('type', 'id')
            ->map(function ($t) {
                if ($t instanceof QuestionType) {
                    return $t->value;
                }

                return (string) $t;
            })
            ->toArray();
    }

    #[Computed]
    public function methodologyStructure()
    {
        switch ($this->methodology->type) {
            case 'simple':
                return $this->getSimpleStructure();
            case 'complex':
                return $this->getComplexStructure();
            case 'twoSection':
                return $this->getTwoSectionStructure();
            default:
                return $this->getComplexStructure(); // fallback
        }
    }

    private function getSimpleStructure()
    {
        return [
            'type' => 'simple',
            'modules' => $this->methodology
                ->modules()
                ->with([
                    'questions' => function ($q) {
                        $q->orderBy('module_question.sequence', 'asc')->orderBy('module_question.id', 'asc');
                    },
                ])
                ->get(),
        ];
    }

    private function getComplexStructure()
    {
        return [
            'type' => 'complex',
            'pillars' => $this->methodology
                ->pillars()
                ->with([
                    'modules' => function ($m) {
                        // Get modules through pillar_module pivot table for this specific methodology
                        $m->wherePivot('methodology_id', $this->methodology->id)
                            ->with(['questions' => function ($q) {
                                $q->where('module_question.methodology_id', $this->methodology->id)
                                    ->orderBy('module_question.sequence', 'asc')
                                    ->orderBy('module_question.id', 'asc');
                            }]);
                    },
                ])
                ->orderBy('methodology_pillar.sequence', 'asc')
                ->get(),
        ];
    }

    private function getTwoSectionStructure()
    {
        $firstSection = $this->methodology
            ->pillars()
            ->where('methodology_pillar.section', 'first')
            ->with([
                'modules' => function ($m) {
                    // Get modules through pillar_module pivot table for this specific methodology
                    $m->wherePivot('methodology_id', $this->methodology->id)
                        ->with(['questions' => function ($q) {
                            $q->where('module_question.methodology_id', $this->methodology->id)
                                ->orderBy('module_question.sequence', 'asc')
                                ->orderBy('module_question.id', 'asc');
                        }]);
                },
            ])
            ->orderBy('methodology_pillar.sequence', 'asc')
            ->get();

        $secondSection = $this->methodology
            ->pillars()
            ->where('methodology_pillar.section', 'second')
            ->with([
                'modules' => function ($m) {
                    // Get modules through pillar_module pivot table for this specific methodology
                    $m->wherePivot('methodology_id', $this->methodology->id)
                        ->with(['questions' => function ($q) {
                            $q->where('module_question.methodology_id', $this->methodology->id)
                                ->orderBy('module_question.sequence', 'asc')
                                ->orderBy('module_question.id', 'asc');
                        }]);
                },
            ])
            ->orderBy('methodology_pillar.sequence', 'asc')
            ->get();

        return [
            'type' => 'twoSection',
            'sections' => [
                'first' => [
                    'name' => $this->methodology->first_section_name ?? 'First Section',
                    'pillars' => $firstSection,
                ],
                'second' => [
                    'name' => $this->methodology->second_section_name ?? 'Second Section',
                    'pillars' => $secondSection,
                ],
            ],
        ];
    }

    // Legacy method for backward compatibility - will be removed
    #[Computed]
    public function modules()
    {
        $structure = $this->methodologyStructure;
        if ($structure['type'] === 'simple') {
            return $structure['modules'];
        }

        // For complex types, return empty collection to avoid breaking existing code
        return collect([]);
    }

    #[Computed]
    public function userAnswersAll()
    {
        $methodologyId = $this->methodology->id;

        return UserAnswer::query()
            ->where('user_id', $this->user->id)
            ->where(function ($q) use ($methodologyId) {
                $q->where(function ($qm) use ($methodologyId) {
                    $qm->where('context_type', 'methodology')->where('context_id', $methodologyId);
                })
                    ->orWhere(function ($qp) use ($methodologyId) {
                        $qp->where('context_type', 'pillar')->whereIn('question_id', function ($sub) use ($methodologyId) {
                            $sub->select('question_id')->from('pillar_question')->where('methodology_id', $methodologyId);
                        });
                    })
                    ->orWhere(function ($qmo) use ($methodologyId) {
                        $qmo->where('context_type', 'module')->whereIn('question_id', function ($sub) use ($methodologyId) {
                            $sub->select('question_id')->from('module_question')->where('methodology_id', $methodologyId);
                        });
                    });
            })
            ->with(['question', 'answer'])
            ->get();
    }

    #[Computed]
    public function placementMaps()
    {
        $methodologyId = $this->methodology->id;

        $methodologyQuestions = DB::table('methodology_question')
            ->select('id', 'question_id', 'weight')
            ->where('methodology_id', $methodologyId)
            ->get();

        $pillarQuestions = DB::table('pillar_question')
            ->select('id', 'pillar_id', 'question_id', 'weight')
            ->where('methodology_id', $methodologyId)
            ->get();

        $moduleQuestions = DB::table('module_question')
            ->select('id', 'module_id', 'pillar_id', 'question_id', 'weight')
            ->where('methodology_id', $methodologyId)
            ->get();

        $maps = [
            'methodology' => [],
            'pillar' => [],
            'module' => [],
        ];

        foreach ($methodologyQuestions as $row) {
            $maps['methodology'][(int) $row->question_id] = [
                'placement_id' => (int) $row->id,
                'weight' => (float) $row->weight,
            ];
        }

        foreach ($pillarQuestions as $row) {
            $pillarId = (int) $row->pillar_id;
            $maps['pillar'][$pillarId][(int) $row->question_id] = [
                'placement_id' => (int) $row->id,
                'weight' => (float) $row->weight,
            ];
        }

        foreach ($moduleQuestions as $row) {
            $pillarId = (int) ($row->pillar_id ?? 0);
            $maps['module'][(int) $row->module_id][$pillarId][(int) $row->question_id] = [
                'placement_id' => (int) $row->id,
                'weight' => (float) $row->weight,
            ];
        }

        return $maps;
    }

    #[Computed]
    public function answerWeightMaps()
    {
        $placement = $this->placementMaps;

        $ids = [
            'methodology_question' => array_values(array_column($placement['methodology'], 'placement_id')),
            'pillar_question' => [],
            'module_question' => [],
        ];

        foreach ($placement['pillar'] as $pillarMap) {
            foreach ($pillarMap as $meta) {
                $ids['pillar_question'][] = $meta['placement_id'];
            }
        }
        foreach ($placement['module'] as $pillarGroups) {
            foreach ($pillarGroups as $questions) {
                foreach ($questions as $meta) {
                    $ids['module_question'][] = $meta['placement_id'];
                }
            }
        }

        $maps = [
            'methodology_question' => [],
            'pillar_question' => [],
            'module_question' => [],
        ];

        foreach ($ids as $type => $idList) {
            if (empty($idList)) {
                continue;
            }
            $rows = DB::table('answer_contexts')
                ->select('context_id', 'answer_id', 'weight')
                ->where('context_type', $type)
                ->whereIn('context_id', $idList)
                ->get();

            foreach ($rows as $r) {
                $maps[$type][(int) $r->context_id][(int) $r->answer_id] = (float) $r->weight;
            }
        }

        return $maps;
    }

    public function getGeneralQuestionMeta(int $questionId): array
    {
        $placement = $this->placementMaps['methodology'][$questionId] ?? null;
        $uaCollection = $this->userAnswersAll->filter(function ($a) use ($questionId) {
            return $a->context_type === 'methodology' && (int) $a->context_id === (int) $this->methodology->id && (int) $a->question_id === $questionId;
        });

        $answerWeight = null;
        $answeredAt = null;
        $score = null;

        if ($placement && $uaCollection->isNotEmpty()) {
            $placementId = $placement['placement_id'];
            $totalPossible = isset($this->answerWeightMaps['methodology_question'][$placementId])
                ? count($this->answerWeightMaps['methodology_question'][$placementId])
                : 0;

            $sumSelectedWeights = 0.0;
            foreach ($uaCollection as $ua) {
                $answeredAt = $ua->created_at; // last one wins; they should be similar
                $aw = $this->answerWeightMaps['methodology_question'][$placementId][$ua->answer_id] ?? 0.0;
                $sumSelectedWeights += (float) $aw;
            }

            $qType = $this->generalQuestionTypes[$questionId] ?? '';
            $normalized = $sumSelectedWeights;
            if ($qType === 'MCQMultiple' && $totalPossible > 0) {
                $normalized = $sumSelectedWeights / $totalPossible;
            }

            $answerWeight = $normalized; // report normalized when MCQMultiple
            $score = $placement['weight'] > 0 ? ($normalized * (float) ($placement['weight'])) / ($placement['weight']) : 0.0;
        }

        return [
            'question_weight' => $placement['weight'] ?? null,
            'answer_weight' => $answerWeight,
            'answered_at' => $answeredAt,
            'score' => $score,
        ];
    }

    public function getModuleQuestionMeta(int $moduleId, ?int $pillarId, int $questionId): array
    {
        $pKey = (int) ($pillarId ?? 0);
        $placement = $this->placementMaps['module'][$moduleId][$pKey][$questionId] ?? null;
        $ua = $this->userAnswersAll->first(function ($a) use ($moduleId, $questionId) {
            return $a->context_type === 'module' && (int) $a->context_id === $moduleId && (int) $a->question_id === $questionId;
        });

        $answerWeight = null;
        if ($placement && $ua) {
            $answerWeight = $this->answerWeightMaps['module_question'][$placement['placement_id']][$ua->answer_id] ?? null;
        }

        return [
            'question_weight' => $placement['weight'] ?? null,
            'answer_weight' => $answerWeight,
            'answered_at' => $ua?->created_at,
        ];
    }

    #[Computed]
    public function scores()
    {
        $service = new ResultCalculationOptimizedService;

        return $service->calculateMethodologyResult($this->user->id, $this->methodology->id, true);
    }

    #[Computed]
    public function generalResults(): array
    {
        $service = new ResultCalculationOptimizedService;
        $methodologyResult = $service->calculateMethodologyResult($this->user->id, $this->methodology->id);

        if (! $methodologyResult || ! isset($methodologyResult['summary'])) {
            return ['percentage' => 0.0, 'total_questions' => 0, 'answered_questions' => 0];
        }

        $summary = $methodologyResult['summary'];

        return [
            'percentage' => (float) ($summary['overall_percentage'] ?? 0.0),
            'total_questions' => (int) ($summary['total_questions'] ?? 0),
            'answered_questions' => (int) ($summary['answered_questions'] ?? 0),
        ];
    }

    #[Computed]
    public function chartData()
    {
        $scores = $this->scores;
        $data = [];

        if ($scores && isset($scores['modules']) && count($scores['modules']) > 0) {
            foreach ($scores['modules'] as $module) {
                $data[] = ['x' => $module['name'], 'y' => $module['percentage'] ?? 0];
            }
        } elseif ($scores && isset($scores['pillars']) && count($scores['pillars']) > 0) {
            foreach ($scores['pillars'] as $pillar) {
                $data[] = ['x' => $pillar['name'], 'y' => $pillar['percentage'] ?? 0];
            }
        }

        return $data;
    }

    public function getModulePercentage(int $moduleId, ?int $pillarId = null): ?float
    {
        $scores = $this->scores;

        // For simple type, check the modules array from service
        if ($pillarId === null && $scores && isset($scores['modules'])) {
            foreach ($scores['modules'] as $m) {
                if ((int) ($m['id'] ?? 0) === $moduleId) {
                    return (float) ($m['percentage'] ?? 0);
                }
            }
        }

        // For complex/twoSection types, use the service to calculate module result
        if ($pillarId !== null) {
            $service = new ResultCalculationOptimizedService;
            $moduleResult = $service->calculateModuleResult($this->user->id, $moduleId, $this->methodology->id, $pillarId);

            return $moduleResult ? (float) ($moduleResult['percentage'] ?? 0) : 0.0;
        }

        return null;
    }

    public function getPillarPercentage(int $pillarId): ?float
    {
        $scores = $this->scores;

        // First try to get from service results
        if ($scores && isset($scores['pillars'])) {
            foreach ($scores['pillars'] as $p) {
                if ((int) ($p['id'] ?? 0) === $pillarId) {
                    return (float) ($p['percentage'] ?? 0);
                }
            }
        }

        // If not found in service results, use the service to calculate pillar result
        $service = new ResultCalculationOptimizedService;
        $pillarResult = $service->calculatePillarResult($this->user->id, $pillarId, $this->methodology->id);

        return $pillarResult ? (float) ($pillarResult['percentage'] ?? 0) : 0.0;
    }

    public function getSectionPercentage(string $sectionName): ?float
    {
        $scores = $this->scores;

        // First try to get from service results
        if ($scores && isset($scores['sections'])) {
            foreach ($scores['sections'] as $s) {
                if (($s['name'] ?? '') === $sectionName) {
                    return (float) ($s['percentage'] ?? 0);
                }
            }
        }

        // If not found, use the service to calculate section result
        // Map section name to section number (1 for first, 2 for second)
        $sectionNumber = ($sectionName === ($this->methodology->first_section_name ?? 'First Section')) ? 1 : 2;

        $service = new ResultCalculationOptimizedService;
        $sectionResult = $service->calculateSectionResult($this->user->id, $this->methodology->id, $sectionNumber);

        return $sectionResult ? (float) ($sectionResult['percentage'] ?? 0) : 0.0;
    }

    public function getMethodologyStatus(): string
    {
        $structure = $this->methodologyStructure;

        switch ($structure['type']) {
            case 'simple':
                // Check all modules status
                $modules = $structure['modules'];
                if ($modules->isEmpty()) {
                    return 'not_started';
                }

                $statuses = [];
                foreach ($modules as $module) {
                    $statuses[] = $this->getModuleStatus($module->id);
                }

                return $this->calculateOverallStatus($statuses);

            case 'complex':
            case 'twoSection':
                // Check all pillars status
                $pillars = collect();

                if ($structure['type'] === 'complex') {
                    $pillars = $structure['pillars'];
                } else {
                    // For twoSection, get all pillars from both sections
                    foreach ($structure['sections'] as $section) {
                        $pillars = $pillars->merge($section['pillars']);
                    }
                }

                if ($pillars->isEmpty()) {
                    return 'not_started';
                }

                $statuses = [];
                foreach ($pillars as $pillar) {
                    $statuses[] = $this->getPillarStatus($pillar->id);
                }

                return $this->calculateOverallStatus($statuses);

            default:
                // Fallback to service method
                $service = new ContextStatusService;

                return $service->getMethodologyStatus($this->user->id, $this->methodology->id);
        }
    }

    private function calculateOverallStatus(array $statuses): string
    {
        if (empty($statuses)) {
            return 'not_started';
        }

        // If all are not started
        if (count(array_filter($statuses, fn ($s) => $s === 'not_started')) === count($statuses)) {
            return 'not_started';
        }

        // If all are completed
        if (count(array_filter($statuses, fn ($s) => $s === 'completed')) === count($statuses)) {
            return 'completed';
        }

        // Otherwise, in progress (some completed/in progress, some not started)
        return 'in_progress';
    }

    public function getPillarStatus(int $pillarId): string
    {
        $service = new ContextStatusService;

        return $service->getPillarStatus($this->user->id, $pillarId, $this->methodology->id);
    }

    public function getModuleStatus(int $moduleId, ?int $pillarId = null): string
    {
        $service = new ContextStatusService;

        return $service->getModuleStatus($this->user->id, $moduleId, $this->methodology->id, $pillarId);
    }

    public function getSectionStatus(string $sectionKey): string
    {
        // For sections, we calculate status based on all pillars in that section
        $structure = $this->methodologyStructure;
        if ($structure['type'] !== 'twoSection' || ! isset($structure['sections'][$sectionKey]['pillars'])) {
            return 'not_started';
        }

        $pillars = $structure['sections'][$sectionKey]['pillars'];
        if ($pillars->isEmpty()) {
            return 'not_started';
        }

        $statuses = [];
        foreach ($pillars as $pillar) {
            $statuses[] = $this->getPillarStatus($pillar->id);
        }

        // If any pillar is in progress, section is in progress
        if (in_array('in_progress', $statuses)) {
            return 'in_progress';
        }

        // If all pillars are completed, section is completed
        if (count(array_filter($statuses, fn ($s) => $s === 'completed')) === count($statuses)) {
            return 'completed';
        }

        // If some pillars are completed but not all, section is in progress
        if (in_array('completed', $statuses)) {
            return 'in_progress';
        }

        return 'not_started';
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-50 text-green-600',
            'in_progress' => 'bg-yellow-50 text-yellow-600',
            'not_started' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'not_started' => 'Not Started',
            default => 'Unknown',
        };
    }

    #[Computed]
    public function overallScoreData(): array
    {
        $scores = $this->scores;
        $structure = $this->methodologyStructure;

        if (! $scores || ! isset($scores['summary'])) {
            return [
                'percentage' => 0,
                'label' => 'Overall Score',
                'count' => 0,
            ];
        }

        $summary = $scores['summary'];

        // Get count based on methodology type
        $count = 0;
        switch ($structure['type']) {
            case 'simple':
                $count = isset($scores['modules']) ? count($scores['modules']) : 0;
                break;
            case 'complex':
                $count = isset($scores['pillars']) ? count($scores['pillars']) : 0;
                break;
            case 'twoSection':
                $count = isset($scores['sections']) ? count($scores['sections']) : 0;
                break;
            default:
                $count = (int) ($summary['total_questions'] ?? 0);
                break;
        }

        return [
            'percentage' => (float) ($summary['overall_percentage'] ?? 0),
            'label' => 'Overall Score',
            'count' => $count,
        ];
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.users.methodology-user-details');
    }
}
