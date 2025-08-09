<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Module;
use App\Models\Tag;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ModulesTable extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $tagFilter = '';
    public string $tagSearch = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTable' => '$refresh',
        'deleteModule' => 'deleteModule',
    ];

    #[Computed]
    public function modules()
    {
        $query = Module::where('name', 'like', '%'.$this->search.'%')
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('tags', (int) $this->tagFilter);
            })
            ->withCount(['methodologies', 'pillars', 'questions'])
            ->orderBy('id', 'asc');
        return $query->paginate($this->perPage);
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

    public function editModule($moduleId)
    {
        $this->dispatch('edit-module', $moduleId);
    }

    public function openDeleteModuleModal($request)
    {
        $module = Module::findOrFail($request['id']);

        $isUsed = $module->methodologies()->exists() || $module->pillars()->exists() || $module->questions()->exists();

        if ($isUsed) {
            $this->dispatch('show-toast', type: 'error', message: __('messages.cannot_delete_module_used'));
            return;
        }

        $modal = [
            'title' => __('messages.delete_module_title'),
            'message' => __('messages.delete_module_message'),
            'note' => __('messages.delete_module_note'),
            'action' => __('messages.delete_action'),
            'callback' => 'deleteModule',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteModule($request)
    {
        $module = Module::findOrFail($request['id']);
        $module->delete();
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.homepage.tables.modules-table', [
            'modules' => $this->modules,
        ]);
    }
}


