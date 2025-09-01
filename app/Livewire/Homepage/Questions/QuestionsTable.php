<?php

namespace App\Livewire\Homepage\Questions;

use App\Models\Question;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class QuestionsTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload;

    public $search = '';
    public $perPage = 10;
    public $tagFilter = '';
    public $tagSearch = '';
    public $tagSuggestions = [];
    public $showTagSuggestions = false;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'deleteQuestion' => 'deleteQuestion',
        'changeQuestionStatus' => 'changeQuestionStatus',
    ];

    #[Computed]
    public function questions()
    {
        return $this->handleReloadState(function () {
            $query = Question::where('title', 'like', '%'.$this->search.'%')
                ->when($this->tagFilter, function ($q) {
                    $q->whereJsonContains('tags', (int) $this->tagFilter);
                })
                ->withCount(['modules', 'pillars', 'methodologies'])
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();
            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
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

    public function updatedTagSearch()
    {
        if (strlen($this->tagSearch) >= 1) {
            $this->tagSuggestions = Tag::where('title', 'like', '%' . $this->tagSearch . '%')
                ->where('active', true)
                ->limit(7)
                ->get(['id', 'title'])
                ->toArray();
            $this->showTagSuggestions = true;
        } else {
            $this->tagFilter = '';
            $this->tagSuggestions = [];
            $this->showTagSuggestions = false;
        }
    }

    public function selectTagFilter($tagId, $tagTitle)
    {
        $this->tagFilter = (string) $tagId;
        $this->tagSearch = $tagTitle;
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
    }

    public function clearTagFilter()
    {
        $this->tagFilter = '';
        $this->tagSearch = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
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

    public function openQuestionStatusModal($request) {
        $question = Question::findOrFail($request['id']);

        if($request['active']) {
            $title = __('messages.activate_question_title');
            $message = __('messages.activate_question_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            // Check if question is in use
            $isUsed = $question->modules()->exists() || $question->pillars()->exists() || $question->methodologies()->exists();

            if ($isUsed) {
                $this->dispatch('show-toast', type: 'error', message: __('messages.cannot_deactivate_question_used'));
                return;
            }

            $title = __('messages.deactivate_question_title');
            $message = __('messages.deactivate_question_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_question_note');
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeQuestionStatus',
            'object' => $request
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeQuestionStatus($request)
    {
        $question = Question::findOrFail($request['id']);
        $question->active = $request['active'];
        $question->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Question activated successfully!' : 'Question deactivated successfully!');
    }

    public function deleteQuestion($request)
    {
        $question = Question::findOrFail($request['id']);
        $question->delete();
        $this->reloadTable();
    }

    public function render()
    {
        return view('livewire.homepage.questions.questions-table', [
            'questions' => $this->questions,
        ]);
    }
}
