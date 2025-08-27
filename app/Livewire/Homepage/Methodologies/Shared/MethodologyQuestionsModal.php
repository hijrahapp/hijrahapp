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
    public string $questionMode = 'simple'; // 'simple' or 'dynamic' (module context)
    public bool $suppressTagSuggestionsOnce = false;
    public array $questionItemIds = []; // question_id => module_id/pillar_id (general context)
    public string $generalItemKind = ''; // 'module' or 'pillar' for general context
    public array $generalItems = []; // [{id, name}]
    public int $suggestionsPage = 1;
    public int $suggestionsPerPage = 5;
    public int $suggestionsTotal = 0;

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
            // Initialize mode from the appropriate pivot (default simple)
            if (!is_null($this->pillarId)) {
                $pivot = \DB::table('pillar_module')
                    ->where('methodology_id', $methodologyId)
                    ->where('pillar_id', $this->pillarId)
                    ->where('module_id', $moduleId)
                    ->first(['questions_mode']);
            } else {
                $pivot = \DB::table('methodology_module')
                    ->where('methodology_id', $methodologyId)
                    ->where('module_id', $moduleId)
                    ->first(['questions_mode']);
            }
            $this->questionMode = ($pivot && !empty($pivot->questions_mode)) ? (string)$pivot->questions_mode : 'simple';
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
                // Map module_question id -> question_id to scope weights per question
                $mqMap = \DB::table('module_question')
                    ->where('methodology_id', $methodologyId)
                    ->where('module_id', $moduleId)
                    ->when(!is_null($this->pillarId), function ($q) {
                        $q->where('pillar_id', $this->pillarId);
                    }, function ($q) {
                        $q->whereNull('pillar_id');
                    })
                    ->get(['id', 'question_id'])
                    ->keyBy('id');
                foreach ($answerWeights as $aw) {
                    $row = $mqMap->get($aw->context_id);
                    if ($row) {
                        $qid = (int)$row->question_id;
                        $this->answerWeights[$qid][$aw->answer_id] = $this->formatDisplayNumber($aw->weight);
                    }
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
                $this->questionItemIds[$row->question_id] = $row->item_id ?? null;
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
                $methodologyQuestionRows = \DB::table('methodology_question')
                    ->where('methodology_id', $methodologyId)
                    ->get(['id', 'question_id'])
                    ->keyBy('id');
                foreach ($answerWeights as $aw) {
                    $row = $methodologyQuestionRows->get($aw->context_id);
                    if ($row) {
                        $qid = (int)$row->question_id;
                        $this->answerWeights[$qid][$aw->answer_id] = $this->formatDisplayNumber($aw->weight);
                    }
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

            // Load general-context item options (modules for simple; pillars for complex/twoSection)
            $methodology = Methodology::find($methodologyId);
            if ($methodology) {
                $type = (string)$methodology->type;
                if ($type === 'simple') {
                    $this->generalItemKind = 'module';
                    $items = $methodology->modules()->get(['modules.id', 'modules.name']);
                } else {
                    $this->generalItemKind = 'pillar';
                    $pillarsQuery = $methodology->pillars();
                    if ($type === 'twoSection') {
                        $pillarsQuery->wherePivot('section', 'first');
                    }
                    $items = $pillarsQuery->get(['pillars.id', 'pillars.name']);
                }
                $this->generalItems = $items->map(function ($it) {
                    return ['id' => $it->id, 'name' => $it->name];
                })->toArray();
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

    public function addQuestion(int $questionId): void
    {
        // Prevent adding Multiple Select questions in dynamic mode (only for module context)
        if ($this->moduleId !== null && $this->questionMode === 'dynamic') {
            $question = Question::find($questionId);
            $typeEnum = $question?->type;
            if ($typeEnum instanceof QuestionType && $typeEnum === QuestionType::MCQMultiple) {
                $this->dispatch('show-toast', type: 'error', message: 'In Dynamic mode, Multiple Select questions are not allowed.');
                return;
            }
        }
        if (in_array($questionId, $this->selectedQuestionIds, true)) {
            return;
        }
        $this->selectedQuestionIds[] = $questionId;
        $this->sequences[$questionId] = count($this->selectedQuestionIds);
        if (!isset($this->questionWeights[$questionId])) {
            $this->questionWeights[$questionId] = '0';
        }
        if ($this->moduleId === null && !isset($this->questionItemIds[$questionId])) {
            $this->questionItemIds[$questionId] = '';
        }
    }

    public function removeQuestion(int $questionId): void
    {
        if (!in_array($questionId, $this->selectedQuestionIds, true)) {
            return;
        }
        // Prevent removing if any answer depends on this question
        $dependentAnswer = collect($this->answerDependencies)
            ->first(fn($qId) => (int)$qId === $questionId);
        if ($dependentAnswer) {
            $this->dispatch('show-toast', type: 'error', message: 'Remove the dependency before removing this question.');
            return;
        }
        $this->selectedQuestionIds = array_values(array_diff($this->selectedQuestionIds, [$questionId]));
        unset($this->questionWeights[$questionId], $this->sequences[$questionId], $this->answerWeights[$questionId], $this->questionItemIds[$questionId]);
        foreach ($this->selectedQuestionIds as $index => $qid) {
            $this->sequences[$qid] = $index + 1;
        }
    }

    public function save(): void
    {
        // Final guard: prevent circular dependencies before persisting
        if ($this->detectCircularDependency()) {
            $this->dispatch('show-toast', type: 'error', message: 'Circular dependency detected. Please fix dependencies.');
            return;
        }
        // Validate weight ranges for questions and answers (0 - 100 inclusive)
        foreach ($this->selectedQuestionIds as $questionId) {
            $qWeight = (float) ($this->questionWeights[$questionId] ?? 0);
            if ($qWeight < 0 || $qWeight > 100) {
                $this->dispatch('show-toast', type: 'error', message: 'Question weights must be between 0 and 100.');
                return;
            }
        }
        foreach ($this->selectedQuestionIds as $questionId) {
            $question = Question::with('answers:id')->find($questionId);
            $answerIds = $question ? $question->answers->pluck('id') : collect();
            foreach ($answerIds as $aid) {
                $aWeight = (float) ($this->answerWeights[$questionId][$aid] ?? 0);
                if ($aWeight < 0 || $aWeight > 100) {
                    $this->dispatch('show-toast', type: 'error', message: 'Answer weights must be between 0 and 100.');
                    return;
                }
            }
        }
        // Validate 100% totals
        // $totalQuestionWeight = array_sum(array_map('floatval', $this->questionWeights));
        // if (abs($totalQuestionWeight - 100) > 0.001) {
        //     $this->dispatch('show-toast', type: 'error', message: 'Total question weights must sum to 100%.');
        //     return;
        // }

        // Validate general context item selections (required for methodology questions)
        if ($this->moduleId === null && $this->methodologyId !== null) {
            $methodology = Methodology::find($this->methodologyId);
            if ($methodology) {
                $type = (string)$methodology->type;
                foreach ($this->selectedQuestionIds as $qid) {
                    $val = $this->questionItemIds[$qid] ?? '';
                    if ($val === '' || $val === null) {
                        $this->dispatch('show-toast', type: 'error', message: 'Please select a '.($type === 'simple' ? 'module' : 'pillar').' for each question.');
                        return;
                    }
                }
            }
        }

        // Validate per-question answers sum to 100%
        // foreach ($this->selectedQuestionIds as $questionId) {
        //     $question = Question::with('answers:id')->find($questionId);
        //     $answerIds = $question ? $question->answers->pluck('id') : collect();
        //     $sum = 0;
        //     foreach ($answerIds as $aid) {
        //         $sum += (float)($this->answerWeights[$questionId][$aid] ?? 0);
        //     }
        //     if (abs($sum - 100) > 0.001) {
        //         $this->dispatch('show-toast', type: 'error', message: 'Each question’s answers must sum to 100%.');
        //         return;
        //     }
        // }

        if ($this->moduleId !== null) {
            // Persist module context
            // Save selected questions mode on the appropriate pivot
            if (!is_null($this->pillarId)) {
                \DB::table('pillar_module')
                    ->updateOrInsert([
                        'methodology_id' => $this->methodologyId,
                        'pillar_id' => $this->pillarId,
                        'module_id' => $this->moduleId,
                    ], [
                        'questions_mode' => $this->questionMode,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
            } else {
                \DB::table('methodology_module')
                    ->updateOrInsert([
                        'methodology_id' => $this->methodologyId,
                        'module_id' => $this->moduleId,
                    ], [
                        'questions_mode' => $this->questionMode,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
            }
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
                        'weight' => (float)($this->answerWeights[$questionId][$aid] ?? 0),
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
                    'item_id' => (int)($this->questionItemIds[$questionId] ?? 0),
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
                        'weight' => (float)($this->answerWeights[$questionId][$aid] ?? 0),
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
        $this->questionItemIds = [];
        $this->generalItemKind = '';
        $this->generalItems = [];
        $this->suggestionsPage = 1;
        $this->suggestionsTotal = 0;
        $this->questionMode = 'simple';
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
                ->when($this->moduleId !== null && $this->questionMode === 'dynamic', function ($q) {
                    $q->where('type', '!=', QuestionType::MCQMultiple->value);
                })
                ->when($this->tagSearch, function ($q) {
                    $tagIds = Tag::where('title', 'like', '%'.$this->tagSearch.'%')->pluck('id');
                    $q->where(function ($qq) use ($tagIds) {
                        foreach ($tagIds as $id) {
                            $qq->orWhereJsonContains('tags', (int)$id);
                        }
                    });
                })
                ->when(count($this->selectedQuestionIds) > 0, function ($q) {
                    $q->whereNotIn('id', $this->selectedQuestionIds);
                });

            // Pagination for suggestions
            $this->suggestionsTotal = (clone $query)->count('id');
            $perPage = max(1, (int)$this->suggestionsPerPage);
            $maxPage = (int) max(1, (int) ceil($this->suggestionsTotal / $perPage));
            if ($this->suggestionsPage > $maxPage) {
                $this->suggestionsPage = $maxPage;
            }
            $offset = ($this->suggestionsPage - 1) * $perPage;

            $questionSuggestions = $query
                ->orderBy('id')
                ->skip($offset)
                ->take($perPage)
                ->get(['id', 'title', 'type']);
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

        // Reset suggestions pagination when filters change
        if (in_array($name, ['search', 'tagSearch', 'typeFilter'], true)) {
            $this->suggestionsPage = 1;
        }

        // Guard switching modes (module context): prevent switching to dynamic if any Multiple Select selected
        if ($name === 'questionMode' && $this->moduleId !== null) {
            if ($value === 'dynamic') {
                $hasMultiple = Question::whereIn('id', $this->selectedQuestionIds)
                    ->where('type', QuestionType::MCQMultiple->value)
                    ->exists();
                if ($hasMultiple) {
                    $this->questionMode = 'simple';
                    $this->dispatch('show-toast', type: 'error', message: 'Cannot switch to Dynamic while Multiple Select questions are selected. Remove them first.');
                } else {
                    $this->suggestionsPage = 1;
                }
            } else {
                // Switching to simple: block if any dependency configured
                $hasDependency = collect($this->answerDependencies)->filter(function ($v) {
                    return $v !== null && $v !== '' && (int)$v > 0;
                })->isNotEmpty();
                if ($hasDependency) {
                    $this->questionMode = 'dynamic';
                    $this->dispatch('show-toast', type: 'error', message: 'Cannot switch to Simple while dependencies exist. Remove dependencies first.');
                } else {
                    $this->suggestionsPage = 1;
                }
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

    public function prevSuggestionsPage(): void
    {
        if ($this->suggestionsPage > 1) {
            $this->suggestionsPage--;
        }
    }

    public function nextSuggestionsPage(): void
    {
        $perPage = max(1, (int)$this->suggestionsPerPage);
        $maxPage = (int) max(1, (int) ceil($this->suggestionsTotal / $perPage));
        if ($this->suggestionsPage < $maxPage) {
            $this->suggestionsPage++;
        }
    }
}


