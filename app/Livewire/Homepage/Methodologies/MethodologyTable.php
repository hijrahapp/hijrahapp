<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Methodology;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class MethodologyTable extends Component
{
    use WithFileUploads, WithoutUrlPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $tagFilter = '';
    public string $tagSearch = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'deleteMethodology' => 'deleteMethodology',
        'changeMethodologyStatus' => 'changeMethodologyStatus',
    ];

    #[Computed]
    public function methodologies()
    {
        $query = Methodology::where('name', 'like', '%'.$this->search.'%')
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('tags', (int) $this->tagFilter);
            })
            ->withCount(['pillars', 'modules', 'questions'])
            ->orderBy('created_at', 'desc');

        // Use custom pagination without URL caching
        $page = $this->getPage();
        return $query->paginate($this->perPage, ['*'], 'page', $page);
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

    public function editMethodology($methodologyId)
    {
        $this->dispatch('edit-methodology', $methodologyId);
    }

    public function openDeleteMethodologyModal($request)
    {
        $methodology = Methodology::findOrFail($request['id']);

        $modal = [
            'title' => __('messages.delete_methodology_title'),
            'message' => __('messages.delete_methodology_message'),
            'note' => __('messages.delete_methodology_note'),
            'action' => __('messages.delete_action'),
            'callback' => 'deleteMethodology',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function openMethodologyStatusModal($request) {
        $methodology = Methodology::findOrFail($request['id']);

        if($request['active']) {
            $title = __('messages.activate_methodology_title');
            $message = __('messages.activate_methodology_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            $title = __('messages.deactivate_methodology_title');
            $message = __('messages.deactivate_methodology_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_methodology_note');
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeMethodologyStatus',
            'object' => $request
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeMethodologyStatus($request)
    {
        $methodology = Methodology::findOrFail($request['id']);
        $methodology->active = $request['active'];
        $methodology->save();
        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Methodology activated successfully!' : 'Methodology deactivated successfully!');
    }

    public function deleteMethodology($request)
    {
        $methodology = Methodology::findOrFail($request['id']);

        // Delete all associations
        $methodology->pillars()->detach();
        $methodology->modules()->detach();
        $methodology->questions()->detach();

        $methodology->delete();
        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: 'Methodology deleted successfully!');
    }

    public function managePillars($methodologyId)
    {
        $this->dispatch('manage-pillars', $methodologyId);
    }

    public function manageModules($methodologyId)
    {
        $this->dispatch('manage-modules', $methodologyId);
    }

    public function manageSections($methodologyId)
    {
        $this->dispatch('manage-sections', $methodologyId);
    }

    public function manageGeneralQuestions($methodologyId)
    {
        return redirect()->route('methodology.questions', ['methodologyId' => $methodologyId]);
    }

    public function manageModuleQuestions($methodologyId)
    {
        $this->dispatch('manage-module-questions', $methodologyId);
    }

    public function viewUsers($methodologyId)
    {
        $this->dispatch('view-users', $methodologyId);
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodology-table', [
            'methodologies' => $this->methodologies,
        ]);
    }
}
