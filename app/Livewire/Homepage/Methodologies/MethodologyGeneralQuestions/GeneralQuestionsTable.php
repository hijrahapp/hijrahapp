<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyGeneralQuestions;

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

    public function getQuestions()
    {
        return $this->handleReloadState(function () {
            $ids = \DB::table('methodology_question')
                ->where('methodology_id', $this->methodologyId)
                ->orderBy('sequence')
                ->pluck('question_id')
                ->toArray();

            if (empty($ids)) {
                return collect();
            }

            $ids = array_map('intval', $ids);

            $q = Question::query()
                ->whereIn('id', $ids)
                ->when($this->search, fn($qq) => $qq->where('title', 'like', '%'.$this->search.'%'))
                ->when($this->tagFilter, fn($qq) => $qq->whereJsonContains('tags', (int)$this->tagFilter))
                ->orderByRaw('FIELD(id, '.implode(',', $ids).')')
                ->get();

            return $q;
        });
    }

    public function render()
    {
        $questions = $this->getQuestions();
        return view('livewire.homepage.methodologies.methodologyGeneralQuestions.general-questions-table', compact('questions'));
    }
}


