<?php

namespace App\Livewire\Homepage\Methodologies\Shared;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Question;
use App\Models\Tag;
use Livewire\Component;

class MethodologyQuestionsModal extends Component
{
    public ?int $methodologyId = null;
    public ?int $moduleId = null;
    public ?int $pillarId = null;

    public string $search = '';
    public string $tagSearch = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;
    public string $typeFilter = '';
    public array $selectedQuestionIds = [];

    public array $questionWeights = []; // question_id => weight
    public array $answerWeights = [];   // answer_id => weight
    public array $answerDependencies = []; // answer_id => dependent_question_id
    public array $sequences = []; // question_id => sequence
    public bool $suppressTagSuggestionsOnce = false;

    protected $listeners = [
        'open-manage-methodology-module-questions' => 'open',
        'open-questions-config' => 'open',
    ];

    public function open(int $methodologyId, ?int $moduleId = null, ?int $pillarId = null): void
    {
        $this->resetState();
        $this->methodologyId = $methodologyId;
        $this->moduleId = $moduleId;
        $this->pillarId = $pillarId;

        if ($this->moduleId !== null) {
            // Module context
            $existing = \DB::table('module_question')
                ->where('methodology_id', $methodologyId)
                ->where('module_id', $moduleId)
                ->when(!is_null($this->pillarId), function ($q) {
                    $q->where('pillar_id', $this->pillarId);
                }, function ($q) {
                    $q->whereNull('pillar_id');
                })
                ->orderBy('sequence')
                ->get();
            $this->selectedQuestionIds = $existing->pluck('question_id')->toArray();
            foreach ($existing as $row) {
                $this->questionWeights[$row->question_id] = $this->formatDisplayNumber($row->weight);
                $this->sequences[$row->question_id] = $row->sequence ?? null;
            }

            $mqIds = \DB::table('module_question')
                ->where('methodology_id', $methodologyId)
                ->where('module_id', $moduleId)
                ->when(!is_null($this->pillarId), function ($q) {
                    $q->where('pillar_id', $this->pillarId);
                }, function ($q) {
                    $q->whereNull('pillar_id');
                })
                ->pluck('id');
            if ($mqIds->count() > 0) {
                $answerWeights = \DB::table('answer_contexts')
                    ->where('context_type', 'module_question')
                    ->whereIn('context_id', $mqIds)
                    ->get();
                foreach ($answerWeights as $aw) {
                    $this->answerWeights[$aw->answer_id] = $this->formatDisplayNumber($aw->weight);
                }
            }
            // Load dependencies from answer_contexts by mapping dependent context id back to question id
            $moduleQuestionRows = \DB::table('module_question')
                ->where('methodology_id', $methodologyId)
                ->where('module_id', $moduleId)
                ->when(!is_null($this->pillarId), function ($q) {
                    $q->where('pillar_id', $this->pillarId);
                }, function ($q) {
                    $q->whereNull('pillar_id');
                })
                ->get(['id', 'question_id'])
                ->keyBy('id');
            $deps = \DB::table('answer_contexts')
                ->where('context_type', 'module_question')
                ->whereIn('context_id', $mqIds)
                ->whereNotNull('dependent_context_type')
                ->whereNotNull('dependent_context_id')
                ->get(['answer_id', 'dependent_context_type', 'dependent_context_id']);
            foreach ($deps as $dep) {
                if ($dep->dependent_context_type === 'module_question') {
                    $target = $moduleQuestionRows->get($dep->dependent_context_id);
                    if ($target) {
                        $this->answerDependencies[$dep->answer_id] = $target->question_id;
                    }
                }
            }
        } else {
            // Methodology (general) context
            $existing = \DB::table('methodology_question')
                ->where('methodology_id', $methodologyId)
                ->orderBy('sequence')
                ->get();
            $this->selectedQuestionIds = $existing->pluck('question_id')->toArray();
            foreach ($existing as $row) {
                $this->questionWeights[$row->question_id] = $this->formatDisplayNumber($row->weight);
            }
            // Initialize sequences based on current selection order
            foreach ($this->selectedQuestionIds as $index => $qid) {
                $this->sequences[$qid] = $index + 1;
            }
            $mqIds = \DB::table('methodology_question')
                ->where('methodology_id', $methodologyId)
                ->pluck('id');
            if ($mqIds->count() > 0) {
                $answerWeights = \DB::table('answer_contexts')
                    ->where('context_type', 'methodology_question')
                    ->whereIn('context_id', $mqIds)
                    ->get();
                foreach ($answerWeights as $aw) {
                    $this->answerWeights[$aw->answer_id] = $this->formatDisplayNumber($aw->weight);
                }
            }
            // Load methodology-level dependencies from answer_contexts
            $methodologyQuestionRows = \DB::table('methodology_question')
                ->where('methodology_id', $methodologyId)
                ->get(['id', 'question_id'])
                ->keyBy('id');
            $deps = \DB::table('answer_contexts')
                ->where('context_type', 'methodology_question')
                ->whereIn('context_id', $mqIds)
                ->whereNotNull('dependent_context_type')
                ->whereNotNull('dependent_context_id')
                ->get(['answer_id', 'dependent_context_type', 'dependent_context_id']);
            foreach ($deps as $dep) {
                if ($dep->dependent_context_type === 'methodology_question') {
                    $target = $methodologyQuestionRows->get($dep->dependent_context_id);
                    if ($target) {
                        $this->answerDependencies[$dep->answer_id] = $target->question_id;
                    }
                }
            }
        }
    }

    public function updatedTagSearch(): void
    {
        if ($this->suppressTagSuggestionsOnce) {
            // One-time suppression after selecting a suggestion
            $this->suppressTagSuggestionsOnce = false;
            $this->showTagSuggestions = false;
            return;
        }

        if (strlen($this->tagSearch) >= 1) {
            $this->tagSuggestions = Tag::where('title', 'like', '%'.$this->tagSearch.'%')
                ->where('active', true)
                ->limit(7)
                ->get(['id', 'title'])
                ->toArray();
            $this->showTagSuggestions = true;
        } else {
            $this->tagSearch = '';
            $this->showTagSuggestions = false;
        }
    }

    public function selectTagSuggestion(string $title): void
    {
        $this->suppressTagSuggestionsOnce = true;
        $this->tagSearch = $title;
        $this->showTagSuggestions = false;
    }

    public function toggleQuestion(int $questionId): void
    {
        if (in_array($questionId, $this->selectedQuestionIds, true)) {
            // Prevent removing if any answer depends on this question
            $dependentAnswer = collect($this->answerDependencies)
                ->first(fn($qId) => (int)$qId === $questionId);
            if ($dependentAnswer) {
                $this->dispatch('show-toast', type: 'error', message: 'Remove the dependency before removing this question.');
                // Instruct UI to revert checkbox toggle
                $this->dispatch('revert-question-selection', id: $questionId);
                return;
            }
            $this->selectedQuestionIds = array_values(array_diff($this->selectedQuestionIds, [$questionId]));
            unset($this->questionWeights[$questionId], $this->sequences[$questionId]);
            // Re-index sequences after removal
            foreach ($this->selectedQuestionIds as $index => $qid) {
                $this->sequences[$qid] = $index + 1;
            }
        } else {
            $this->selectedQuestionIds[] = $questionId;
            // Assign next sequence number on add
            $this->sequences[$questionId] = count($this->selectedQuestionIds);
            // Initialize weight if empty
            if (!isset($this->questionWeights[$questionId])) {
                $this->questionWeights[$questionId] = '0';
            }
        }
    }

    public function save(): void
    {
        // Final guard: prevent circular dependencies before persisting
        if ($this->detectCircularDependency()) {
            $this->dispatch('show-toast', type: 'error', message: 'Circular dependency detected. Please fix dependencies.');
            return;
        }
        // Validate 100% totals
        $totalQuestionWeight = array_sum(array_map('floatval', $this->questionWeights));
        if (abs($totalQuestionWeight - 100) > 0.001) {
            $this->dispatch('show-toast', type: 'error', message: 'Total question weights must sum to 100%.');
            return;
        }

        // Validate per-question answers sum to 100%
        foreach ($this->selectedQuestionIds as $questionId) {
            $question = Question::with('answers:id')->find($questionId);
            $answerIds = $question ? $question->answers->pluck('id') : collect();
            $sum = 0;
            foreach ($answerIds as $aid) {
                $sum += (float)($this->answerWeights[$aid] ?? 0);
            }
            if (abs($sum - 100) > 0.001) {
                $this->dispatch('show-toast', type: 'error', message: 'Each question’s answers must sum to 100%.');
                return;
            }
        }

        if ($this->moduleId !== null) {
            // Persist module context
            // Clean up old answer contexts tied to this module-question set
            $oldMqIds = \DB::table('module_question')
                ->where('methodology_id', $this->methodologyId)
                ->where('module_id', $this->moduleId)
                ->when(!is_null($this->pillarId), function ($q) {
                    $q->where('pillar_id', $this->pillarId);
                }, function ($q) {
                    $q->whereNull('pillar_id');
                })
                ->pluck('id');
            if ($oldMqIds->count() > 0) {
                \DB::table('answer_contexts')
                    ->where('context_type', 'module_question')
                    ->whereIn('context_id', $oldMqIds)
                    ->delete();
            }
            \DB::table('module_question')
                ->where('methodology_id', $this->methodologyId)
                ->where('module_id', $this->moduleId)
                ->when(!is_null($this->pillarId), function ($q) {
                    $q->where('pillar_id', $this->pillarId);
                }, function ($q) {
                    $q->whereNull('pillar_id');
                })
                ->delete();

            $questionIdToContextId = [];
            // Pass 1: create all context rows
            foreach ($this->selectedQuestionIds as $questionId) {
                $mqId = \DB::table('module_question')->insertGetId([
                    'methodology_id' => $this->methodologyId,
                    'module_id' => $this->moduleId,
                    'pillar_id' => $this->pillarId,
                    'question_id' => $questionId,
                    'weight' => (float)($this->questionWeights[$questionId] ?? 0),
                    'sequence' => (int)($this->sequences[$questionId] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $questionIdToContextId[$questionId] = $mqId;
            }
            // Pass 2: upsert answer contexts with dependencies
            foreach ($this->selectedQuestionIds as $questionId) {
                $mqId = $questionIdToContextId[$questionId];
                $question = Question::with('answers:id')->find($questionId);
                $answerIds = $question ? $question->answers->pluck('id') : collect();
                foreach ($answerIds as $aid) {
                    $dependentQuestionId = $this->answerDependencies[$aid] ?? null;
                    $dependentContextId = $dependentQuestionId ? ($questionIdToContextId[$dependentQuestionId] ?? null) : null;
                    \DB::table('answer_contexts')->updateOrInsert([
                        'context_type' => 'module_question',
                        'context_id' => $mqId,
                        'answer_id' => $aid,
                    ], [
                        'weight' => (float)($this->answerWeights[$aid] ?? 0),
                        'dependent_context_type' => $dependentContextId ? 'module_question' : null,
                        'dependent_context_id' => $dependentContextId,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            }
        } else {
            // Persist methodology (general) context
            $oldMqIds = \DB::table('methodology_question')
                ->where('methodology_id', $this->methodologyId)
                ->pluck('id');
            if ($oldMqIds->count() > 0) {
                \DB::table('answer_contexts')
                    ->where('context_type', 'methodology_question')
                    ->whereIn('context_id', $oldMqIds)
                    ->delete();
            }
            \DB::table('methodology_question')
                ->where('methodology_id', $this->methodologyId)
                ->delete();

            $questionIdToContextId = [];
            // Pass 1: create all context rows
            foreach ($this->selectedQuestionIds as $questionId) {
                $mqId = \DB::table('methodology_question')->insertGetId([
                    'methodology_id' => $this->methodologyId,
                    'question_id' => $questionId,
                    'weight' => (float)($this->questionWeights[$questionId] ?? 0),
                    'sequence' => (int)($this->sequences[$questionId] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $questionIdToContextId[$questionId] = $mqId;
            }
            // Pass 2: upsert answer contexts with dependencies
            foreach ($this->selectedQuestionIds as $questionId) {
                $mqId = $questionIdToContextId[$questionId];
                $question = Question::with('answers:id')->find($questionId);
                $answerIds = $question ? $question->answers->pluck('id') : collect();
                foreach ($answerIds as $aid) {
                    $dependentQuestionId = $this->answerDependencies[$aid] ?? null;
                    $dependentContextId = $dependentQuestionId ? ($questionIdToContextId[$dependentQuestionId] ?? null) : null;
                    \DB::table('answer_contexts')->updateOrInsert([
                        'context_type' => 'methodology_question',
                        'context_id' => $mqId,
                        'answer_id' => $aid,
                    ], [
                        'weight' => (float)($this->answerWeights[$aid] ?? 0),
                        'dependent_context_type' => $dependentContextId ? 'methodology_question' : null,
                        'dependent_context_id' => $dependentContextId,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            }

            // Dependencies are persisted within answer_contexts for methodology context
        }

        $this->dispatch('show-toast', type: 'success', message: 'Questions updated successfully');
        $this->dispatch('refreshTable');
        $this->dispatch('click');
    }

    public function reorderSelectedQuestions(array $orderedIds): void
    {
        // Keep only ids that are in current selection and in provided order
        $filtered = array_values(array_intersect($orderedIds, $this->selectedQuestionIds));
        $this->selectedQuestionIds = $filtered;
        // Update sequences to reflect new ordering (1-based)
        foreach ($this->selectedQuestionIds as $index => $qid) {
            $this->sequences[$qid] = $index + 1;
        }
    }

    public function moveQuestionUp(int $questionId): void
    {
        $index = array_search($questionId, $this->selectedQuestionIds, true);
        if ($index === false || $index === 0) {
            return;
        }
        $prevIndex = $index - 1;
        [$this->selectedQuestionIds[$prevIndex], $this->selectedQuestionIds[$index]] = [
            $this->selectedQuestionIds[$index],
            $this->selectedQuestionIds[$prevIndex],
        ];
        // Reindex sequences
        foreach ($this->selectedQuestionIds as $i => $qid) {
            $this->sequences[$qid] = $i + 1;
        }
    }

    public function moveQuestionDown(int $questionId): void
    {
        $index = array_search($questionId, $this->selectedQuestionIds, true);
        if ($index === false || $index >= count($this->selectedQuestionIds) - 1) {
            return;
        }
        $nextIndex = $index + 1;
        [$this->selectedQuestionIds[$nextIndex], $this->selectedQuestionIds[$index]] = [
            $this->selectedQuestionIds[$index],
            $this->selectedQuestionIds[$nextIndex],
        ];
        // Reindex sequences
        foreach ($this->selectedQuestionIds as $i => $qid) {
            $this->sequences[$qid] = $i + 1;
        }
    }

    public function resetState(): void
    {
        $this->resetErrorBag();
        $this->search = '';
        $this->tagSearch = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
        $this->typeFilter = '';
        $this->selectedQuestionIds = [];
        $this->questionWeights = [];
        $this->answerWeights = [];
        $this->answerDependencies = [];
        $this->sequences = [];
    }

    public function render()
    {
        $questionSuggestions = collect();

        if ($this->methodologyId !== null) {
            // Suggestions list for active/filterable questions (search across ALL active questions)
            $query = Question::query()->where('active', true)
                ->when($this->search, fn($q) => $q->where('title', 'like', '%'.$this->search.'%'))
                ->when($this->typeFilter, function ($q) {
                    $q->where('type', '=', (string)$this->typeFilter);
                })
                ->when($this->tagSearch, function ($q) {
                    $tagIds = Tag::where('title', 'like', '%'.$this->tagSearch.'%')->pluck('id');
                    $q->where(function ($qq) use ($tagIds) {
                        foreach ($tagIds as $id) {
                            $qq->orWhereJsonContains('tags', (int)$id);
                        }
                    });
                })
                ->limit(6)
                ->get(['id', 'title', 'type']);
            $questionSuggestions = $query;
        }

        $questionTypes = array_map(function (QuestionType $type) {
            return [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ];
        }, QuestionType::cases());

        return view('livewire.homepage.methodologies.shared.methodology-questions-modal', [
            'questionSuggestions' => $questionSuggestions,
            'questionTypes' => $questionTypes,
        ]);
    }

    public function updated($name, $value): void
    {
        // Intercept dependency selection changes to prevent circular graphs
        if (str_starts_with($name, 'answerDependencies.')) {
            // Self edge prevention: answer cannot lead to its own question
            $answerId = (int) str_replace('answerDependencies.', '', $name);
            $answerToQuestion = $this->buildAnswerToQuestionMap();
            $fromQuestionId = $answerToQuestion[$answerId] ?? null;
            // Normalize the stored value to int when non-empty, otherwise keep empty string
            $this->answerDependencies[$answerId] = ($value === '' || $value === null) ? '' : (int)$value;
            if ($fromQuestionId && (int)$value === $fromQuestionId) {
                // Revert to empty string to align with <option value="">No dependency</option>
                $this->answerDependencies[$answerId] = '';
                $this->dispatch('show-toast', type: 'error', message: 'An answer cannot lead to its own question.');
                return;
            }

            // Cycle prevention
            if ($this->detectCircularDependency()) {
                // Revert to empty string to ensure the UI select reflects "No dependency"
                $this->answerDependencies[$answerId] = '';
                $this->dispatch('show-toast', type: 'error', message: 'Circular dependency detected. Selection reverted.');
            }
        }
    }

    private function buildAnswerToQuestionMap(): array
    {
        $map = [];
        foreach ($this->selectedQuestionIds as $qid) {
            $question = Question::with('answers:id')->find($qid);
            if ($question) {
                foreach ($question->answers as $ans) {
                    $map[$ans->id] = $qid;
                }
            }
        }
        return $map;
    }

    private function buildQuestionGraph(): array
    {
        // Build directed graph: fromQuestionId => [dependentQuestionIds]
        $graph = [];
        $answerToQuestion = $this->buildAnswerToQuestionMap();
        foreach ($this->selectedQuestionIds as $qid) {
            $graph[$qid] = $graph[$qid] ?? [];
        }
        foreach ($this->answerDependencies as $answerId => $dependentQuestionId) {
            if (!$dependentQuestionId) {
                continue;
            }
            $fromQ = $answerToQuestion[(int)$answerId] ?? null;
            $depQ = (int)$dependentQuestionId;
            if ($fromQ && in_array($depQ, $this->selectedQuestionIds, true)) {
                $graph[$fromQ][] = $depQ;
            }
        }
        return $graph;
    }

    private function detectCircularDependency(): bool
    {
        $graph = $this->buildQuestionGraph();
        $visited = [];
        $inStack = [];
        $self = $this;
        $dfs = function ($node) use (&$dfs, &$visited, &$inStack, $graph) {
            $visited[$node] = true;
            $inStack[$node] = true;
            foreach ($graph[$node] ?? [] as $nbr) {
                if (!($visited[$nbr] ?? false) && $dfs($nbr)) {
                    return true;
                } elseif ($inStack[$nbr] ?? false) {
                    return true;
                }
            }
            $inStack[$node] = false;
            return false;
        };

        foreach (array_keys($graph) as $node) {
            if (!($visited[$node] ?? false)) {
                if ($dfs($node)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function formatDisplayNumber(null|int|float|string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $str = (string)$value;
        if (strpos($str, '.') !== false) {
            $str = rtrim(rtrim($str, '0'), '.');
        }
        return $str;
    }
}


