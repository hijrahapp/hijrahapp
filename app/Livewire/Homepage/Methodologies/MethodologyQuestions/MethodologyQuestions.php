<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyQuestions;

use App\Enums\QuestionType;
use App\Models\Methodology;
use App\Models\Question;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.index')]
class MethodologyQuestions extends Component
{
    use WithFileUploads, WithoutUrlPagination;

    public int $methodologyId;
    public string $search = '';
    public string $typeFilter = '';
    public string $tagFilter = '';
    public string $tagSearch = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;
    public int $perPage = 10;

    // Add/Edit Question properties
    public int $editingQuestionId = 0;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'deleteMethodologyQuestion' => 'deleteQuestion',
        'changeQuestionStatus' => 'changeQuestionStatus',
        'reorderQuestions' => 'reorderQuestions',
        'modal-closed' => 'resetEditingQuestion',
    ];

    public function mount($methodologyId)
    {
        $this->methodologyId = $methodologyId;

        // Check if methodology exists
        $methodology = Methodology::find($methodologyId);
        if (!$methodology) {
            $this->dispatch('show-toast', type: 'error', message: 'Methodology not found!');
            return redirect()->route('homepage.index');
        }

        $this->loadAvailableQuestions();
    }

    #[Computed]
    public function methodology()
    {
        return Methodology::with(['pillars.modules', 'modules', 'questions.answers'])->find($this->methodologyId);
    }

    #[Computed]
    public function methodologyQuestions()
    {
        $query = $this->methodology->questions()
            ->where('title', 'like', '%' . $this->search . '%')
            ->when($this->typeFilter, function ($q) {
                $q->where('type', $this->typeFilter);
            })
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('tags', (int) $this->tagFilter);
            })
            ->with(['answers', 'pillars', 'modules'])
            ->orderBy('methodology_question.sequence', 'asc')
            ->orderBy('questions.id', 'asc');

        $page = $this->getPage();
        return $query->paginate($this->perPage, ['*'], 'page', $page);
    }

    #[Computed]
    public function pillars()
    {
        return $this->methodology->pillars;
    }

    #[Computed]
    public function modules()
    {
        return $this->methodology->modules;
    }

    public function loadAvailableQuestions()
    {
        $methodology = Methodology::with('questions')->find($this->methodologyId);
        if ($methodology) {
            $this->availableQuestions = Question::where('active', true)
                ->whereNotIn('id', $methodology->questions->pluck('id'))
                ->get(['id', 'title', 'type'])
                ->toArray();
        }
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



    public function openDeleteModal($questionId)
    {
        $question = $this->methodology->questions()->find($questionId);
        if (!$question) {
            $this->dispatch('show-toast', type: 'error', message: 'Question not found!');
            return;
        }

        $modal = [
            'title' => 'Delete Question',
            'message' => 'Are you sure you want to delete this question from the methodology?',
            'note' => 'This action cannot be undone.',
            'action' => 'Delete',
            'callback' => 'deleteMethodologyQuestion',
            'object' => ['id' => $questionId]
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteQuestion($request)
    {
        $questionId = $request['id'];
        $question = $this->methodology->questions()->find($questionId);

        if (!$question) {
            $this->dispatch('show-toast', type: 'error', message: 'Question not found!');
            return;
        }

        // Remove from methodology
        $this->methodology->questions()->detach($questionId);

        // Remove from pillars and modules
        $question->pillars()->wherePivot('methodology_id', $this->methodologyId)->detach();
        $question->modules()->wherePivot('methodology_id', $this->methodologyId)->detach();

        $this->loadAvailableQuestions();
        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: 'Question deleted successfully!');
    }

    public function changeQuestionStatus($request)
    {
        $questionId = $request['id'];
        $question = Question::find($questionId);

        if (!$question) {
            $this->dispatch('show-toast', type: 'error', message: 'Question not found!');
            return;
        }

        $question->active = $request['active'];
        $question->save();

        $this->loadAvailableQuestions();
        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Question activated successfully!' : 'Question deactivated successfully!');
    }

    public function reorderQuestions($questionIds)
    {
        try {
            foreach ($questionIds as $index => $questionId) {
                // Update the order in the pivot table
                $this->methodology->questions()->updateExistingPivot($questionId, [
                    'sequence' => $index + 1
                ]);
            }

            $this->dispatch('refreshTable');
            $this->dispatch('show-toast', type: 'success', message: 'Questions reordered successfully!');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error reordering questions: ' . $e->getMessage());
        }
    }

    public function setEditingQuestion($questionId)
    {
        $this->editingQuestionId = $questionId;
    }

    public function resetEditingQuestion()
    {
        $this->editingQuestionId = 0;
    }

    public function getQuestionTypes()
    {
        return collect(QuestionType::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ];
        });
    }

    public function getTagTitles($tagIds, $limit = 3)
    {
        if (empty($tagIds) || !is_array($tagIds)) {
            return [
                'tags' => [],
                'hasMore' => false,
                'totalCount' => 0,
                'remainingCount' => 0,
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
            'remainingCount' => max(0, $totalCount - $limit)
        ];
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodologyQuestions.methodology-questions', [
            'methodology' => $this->methodology,
            'methodologyQuestions' => $this->methodologyQuestions,
            'pillars' => $this->pillars,
            'modules' => $this->modules,
            'questionTypes' => $this->getQuestionTypes(),
        ]);
    }
}
