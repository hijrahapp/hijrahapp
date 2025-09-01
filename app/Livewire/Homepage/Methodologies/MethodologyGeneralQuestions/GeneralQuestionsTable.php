<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyGeneralQuestions;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\Tag;
use App\Traits\HasTagTitles;
use App\Traits\WithTableReload;
use Livewire\Component;

class GeneralQuestionsTable extends Component
{
    use HasTagTitles, WithTableReload;

    public int $methodologyId;

    public string $search = '';

    public string $tagFilter = '';

    public string $tagSearch = '';

    public array $tagSuggestions = [];

    public bool $showTagSuggestions = false;

    public ?string $methodologyType = null;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
    ];

    public function updatedTagSearch(): void
    {
        if (strlen($this->tagSearch) >= 1) {
            $this->tagSuggestions = Tag::where('title', 'like', '%'.$this->tagSearch.'%')
                ->where('active', true)
                ->limit(7)
                ->get(['id', 'title'])
                ->toArray();
            $this->showTagSuggestions = true;
        } else {
            $this->tagFilter = '';
            $this->showTagSuggestions = false;
            $this->resetPageAndReload();
        }
    }

    public function selectTagFilter($tagId, $tagTitle): void
    {
        $this->tagFilter = (string) $tagId;
        $this->tagSearch = $tagTitle;
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
        $this->resetPageAndReload();
    }

    public function clearTagFilter(): void
    {
        $this->tagFilter = '';
        $this->tagSearch = '';
        $this->showTagSuggestions = false;
        $this->resetPageAndReload();
    }

    public function openConfig(): void
    {
        $this->dispatch('open-manage-methodology-module-questions', methodologyId: $this->methodologyId, moduleId: null);
        $this->dispatch('show-modal', selector: '#methodology_questions_modal');
    }

    public function remove(int $questionId): void
    {
        \DB::table('methodology_question')
            ->where('methodology_id', $this->methodologyId)
            ->where('question_id', $questionId)
            ->orderBy('sequence')
            ->delete();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: 'Removed successfully');
    }

    public function mount(int $methodologyId): void
    {
        $this->methodologyId = $methodologyId;
        $methodology = Methodology::findOrFail($methodologyId);
        $this->methodologyType = $methodology->type;
    }

    public function getQuestions()
    {
        return $this->handleReloadState(function () {
            $methodologyQuestions = \DB::table('methodology_question')
                ->where('methodology_id', $this->methodologyId)
                ->orderBy('sequence')
                ->get(['question_id', 'weight', 'item_id']);

            if ($methodologyQuestions->isEmpty()) {
                return collect();
            }

            $questionIds = $methodologyQuestions->pluck('question_id')->toArray();
            $questionIds = array_map('intval', $questionIds);

            // Get questions with basic data
            $questions = Question::query()
                ->whereIn('id', $questionIds)
                ->when($this->search, fn ($qq) => $qq->where('title', 'like', '%'.$this->search.'%'))
                ->when($this->tagFilter, fn ($qq) => $qq->whereJsonContains('tags', (int) $this->tagFilter))
                ->orderByRaw('FIELD(id, '.implode(',', $questionIds).')')
                ->get();

            // Add methodology question data and item names to each question
            $questionsWithData = $questions->map(function ($question) use ($methodologyQuestions) {
                $methodologyQuestion = $methodologyQuestions->firstWhere('question_id', $question->id);
                $question->weight = $methodologyQuestion->weight ?? 0;
                $question->item_id = $methodologyQuestion->item_id;
                $question->item_name = $this->getItemName($methodologyQuestion->item_id);

                return $question;
            });

            return $questionsWithData;
        });
    }

    private function getItemName(?int $itemId): ?string
    {
        if (! $itemId || ! $this->methodologyType) {
            return null;
        }

        if ($this->methodologyType === 'simple') {
            $module = Module::find($itemId);

            return $module?->name;
        } else {
            // complex or twoSection
            $pillar = Pillar::find($itemId);

            return $pillar?->name;
        }
    }

    public function render()
    {
        $questions = $this->getQuestions();

        return view('livewire.homepage.methodologies.methodologyGeneralQuestions.general-questions-table', compact('questions'));
    }
}
