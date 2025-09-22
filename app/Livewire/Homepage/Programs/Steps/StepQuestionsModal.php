<?php

namespace App\Livewire\Homepage\Programs\Steps;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Step;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StepQuestionsModal extends Component
{
    public ?int $stepId = null;

    public ?Step $step = null;

    public string $search = '';

    public string $tagSearch = '';

    public array $tagSuggestions = [];

    public bool $showTagSuggestions = false;

    public string $typeFilter = '';

    public array $selectedQuestionIds = [];

    public array $correctAnswers = []; // question_id => answer_id

    public array $sequences = []; // question_id => sequence

    public bool $suppressTagSuggestionsOnce = false;

    public int $suggestionsPage = 1;

    public int $suggestionsPerPage = 5;

    public int $suggestionsTotal = 0;

    protected $listeners = [
        'open-step-questions-modal' => 'open',
    ];

    public function open(int $stepId): void
    {
        $this->resetState();
        $this->stepId = $stepId;
        $this->step = Step::find($stepId);

        if (! $this->step) {
            $this->dispatch('show-toast', type: 'error', message: 'Step not found.');

            return;
        }

        // Load existing questions for this step
        $existing = DB::table('step_question')
            ->where('step_id', $stepId)
            ->orderBy('sequence')
            ->get();

        $this->selectedQuestionIds = $existing->pluck('question_id')->toArray();

        foreach ($existing as $row) {
            $this->sequences[$row->question_id] = $row->sequence ?? null;
            $this->correctAnswers[$row->question_id] = $row->correct_answer_id ?? null;
        }

        $this->dispatch('show-modal', selector: '#step_questions_modal');
    }

    public function updatedTagSearch(): void
    {
        if ($this->suppressTagSuggestionsOnce) {
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
        // Prevent adding Multiple Select questions
        $question = Question::find($questionId);
        $typeEnum = $question?->type;
        if ($typeEnum instanceof QuestionType && $typeEnum === QuestionType::MCQMultiple) {
            $this->dispatch('show-toast', type: 'error', message: 'Multiple Select questions are not allowed for quiz steps.');

            return;
        }

        if (in_array($questionId, $this->selectedQuestionIds, true)) {
            return;
        }

        $this->selectedQuestionIds[] = $questionId;
        $this->sequences[$questionId] = count($this->selectedQuestionIds);

        // Set first answer as default correct answer
        // if ($question && $question->answers->count() > 0) {
        // $this->correctAnswers[$questionId] = null;
        // }
    }

    public function removeQuestion(int $questionId): void
    {
        if (! in_array($questionId, $this->selectedQuestionIds, true)) {
            return;
        }

        $this->selectedQuestionIds = array_values(array_diff($this->selectedQuestionIds, [$questionId]));
        unset($this->sequences[$questionId], $this->correctAnswers[$questionId]);

        foreach ($this->selectedQuestionIds as $index => $qid) {
            $this->sequences[$qid] = $index + 1;
        }
    }

    public function save(): void
    {
        // Validate that each question has a correct answer
        foreach ($this->selectedQuestionIds as $questionId) {
            if (! isset($this->correctAnswers[$questionId]) || ! $this->correctAnswers[$questionId]) {
                $this->dispatch('show-toast', type: 'error', message: 'Please select a correct answer for each question.');

                return;
            }
        }

        // Clean up old step questions
        DB::table('step_question')
            ->where('step_id', $this->stepId)
            ->delete();

        // Insert new step questions
        foreach ($this->selectedQuestionIds as $questionId) {
            DB::table('step_question')->insert([
                'step_id' => $this->stepId,
                'question_id' => $questionId,
                'correct_answer_id' => $this->correctAnswers[$questionId],
                'sequence' => $this->sequences[$questionId] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Step questions updated successfully.');
        $this->dispatch('refreshTable');
        $this->dispatch('click');
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
        $this->correctAnswers = [];
        $this->sequences = [];
        $this->suggestionsPage = 1;
        $this->suggestionsTotal = 0;
    }

    public function render()
    {
        $questionSuggestions = collect();

        if ($this->stepId !== null) {
            // Suggestions list for active/filterable questions (exclude MCQMultiple)
            $query = Question::query()->where('active', true)
                ->where('type', '!=', QuestionType::MCQMultiple->value)
                ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
                ->when($this->typeFilter, function ($q) {
                    $q->where('type', '=', (string) $this->typeFilter);
                })
                ->when($this->tagSearch, function ($q) {
                    $tagIds = Tag::where('title', 'like', '%'.$this->tagSearch.'%')->pluck('id');
                    $q->where(function ($qq) use ($tagIds) {
                        foreach ($tagIds as $id) {
                            $qq->orWhereJsonContains('tags', (int) $id);
                        }
                    });
                })
                ->when(count($this->selectedQuestionIds) > 0, function ($q) {
                    $q->whereNotIn('id', $this->selectedQuestionIds);
                });

            // Pagination for suggestions
            $this->suggestionsTotal = (clone $query)->count('id');
            $perPage = max(1, (int) $this->suggestionsPerPage);
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
        }, array_filter(QuestionType::cases(), function (QuestionType $type) {
            return $type !== QuestionType::MCQMultiple;
        }));

        return view('livewire.homepage.programs.steps.step-questions-modal', [
            'questionSuggestions' => $questionSuggestions,
            'questionTypes' => $questionTypes,
        ]);
    }

    public function updated($name, $value): void
    {
        // Reset suggestions pagination when filters change
        if (in_array($name, ['search', 'tagSearch', 'typeFilter'], true)) {
            $this->suggestionsPage = 1;
        }
    }

    public function prevSuggestionsPage(): void
    {
        if ($this->suggestionsPage > 1) {
            $this->suggestionsPage--;
        }
    }

    public function nextSuggestionsPage(): void
    {
        $perPage = max(1, (int) $this->suggestionsPerPage);
        $maxPage = (int) max(1, (int) ceil($this->suggestionsTotal / $perPage));
        if ($this->suggestionsPage < $maxPage) {
            $this->suggestionsPage++;
        }
    }
}
