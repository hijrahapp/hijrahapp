<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Question;
use App\Models\Tag;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class QuestionsTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTable' => '$refresh',
        'deleteQuestion' => 'deleteQuestion',
    ];

    #[Computed]
    public function questions()
    {
        $query = Question::where('title', 'like', '%'.$this->search.'%')
            ->withCount(['modules', 'pillars', 'methodologies'])
            ->orderBy('id', 'asc');
        return $query->paginate($this->perPage);
    }

    public function getTagTitles($tagIds, $limit = 3)
    {
        if (empty($tagIds) || !is_array($tagIds)) {
            return [
                'tags' => [],
                'hasMore' => false,
                'totalCount' => 0
            ];
        }
        
        $allTags = Tag::whereIn('id', $tagIds)
            ->where('active', true)
            ->pluck('title')
            ->toArray();
        
        $totalCount = count($allTags);
        $displayedTags = array_slice($allTags, 0, $limit);
        $hasMore = $totalCount > $limit;
        
        return [
            'tags' => $displayedTags,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'remainingCount' => $totalCount - $limit
        ];
    }

    public function editQuestion($questionId)
    {
        $this->dispatch('edit-question', $questionId);
    }

    public function openDeleteQuestionModal($request) {
        $question = Question::findOrFail($request['id']);

        $isUsed = $question->modules()->exists() || $question->pillars()->exists() || $question->methodologies()->exists();
        
        if ($isUsed) {
            $this->dispatch('show-toast', type: 'error', message: __('messages.cannot_delete_question_used'));
            return;
        }

        $modal = [ 
            'title' => __('messages.delete_question_title'),
            'message' => __('messages.delete_question_message'),
            'note' => __('messages.delete_question_note'),
            'action' => __('messages.delete_action'),
            'callback' => 'deleteQuestion',
            'object' => $request
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteQuestion($request)
    {
        $question = Question::findOrFail($request['id']);
        // Detach question from any related pillars, modules, and methodologies before deletion
        try {
            $question->answers()->detach();
        } catch (\Throwable $e) {}
        try {
            $question->pillars()->detach();
        } catch (\Throwable $e) {}
        try {
            $question->modules()->detach();
        } catch (\Throwable $e) {}
        try {
            $question->methodologies()->detach();
        } catch (\Throwable $e) {}
        $question->delete();
        $this->dispatch('refreshTable');
    }

    public function getSearchProperty()
    {
        return $this->search;
    }

    public function setSearchProperty($value)
    {
        $this->search = $value;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.homepage.tables.questions-table', [
            'questions' => $this->questions,
        ]);
    }
}
