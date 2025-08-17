<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Question;
use App\Models\Tag;
use App\Traits\HasTagTitles;
use Livewire\Component;

class GeneralQuestionsTable extends Component
{
    use HasTagTitles;

    public int $methodologyId;
    public string $search = '';
    public string $tagFilter = '';
    public string $tagSearch = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;

    protected $listeners = [
        'refresh-general-questions' => '$refresh',
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
        }
    }

    public function selectTagFilter($tagId, $tagTitle): void
    {
        $this->tagFilter = (string) $tagId;
        $this->tagSearch = $tagTitle;
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
    }

    public function clearTagFilter(): void
    {
        $this->tagFilter = '';
        $this->tagSearch = '';
        $this->showTagSuggestions = false;
    }

    public function openConfig(): void
    {
        $this->dispatch('open-manage-methodology-module-questions', methodologyId: $this->methodologyId, moduleId: null);
        $this->dispatch('show-modal', selector: '#methodology_module_questions_modal');
    }

    public function remove(int $questionId): void
    {
        \DB::table('methodology_question')
            ->where('methodology_id', $this->methodologyId)
            ->where('question_id', $questionId)
            ->delete();
        $this->dispatch('refresh-general-questions');
        $this->dispatch('show-toast', type: 'success', message: 'Removed successfully');
    }

    public function getQuestions()
    {
        $ids = \DB::table('methodology_question')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('question_id');

        $q = Question::query()
            ->whereIn('id', $ids)
            ->when($this->search, fn($qq) => $qq->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->tagFilter, fn($qq) => $qq->whereJsonContains('tags', (int)$this->tagFilter))
            ->get();

        return $q;
    }

    public function render()
    {
        $questions = $this->getQuestions();
        return view('livewire.homepage.methodologies.general-questions-table', compact('questions'));
    }
}


