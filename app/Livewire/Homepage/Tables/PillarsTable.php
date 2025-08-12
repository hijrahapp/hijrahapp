<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Pillar;
use App\Models\Tag;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PillarsTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 10;
    public $tagFilter = '';
    public $tagSearch = '';
    public $tagSuggestions = [];
    public $showTagSuggestions = false;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTable' => '$refresh',
        'deletePillar' => 'deletePillar',
        'changePillarStatus' => 'changePillarStatus',
    ];

    #[Computed]
    public function pillars()
    {
        $query = Pillar::where('name', 'like', '%'.$this->search.'%')
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('tags', (int) $this->tagFilter);
            })
            ->withCount(['methodologies', 'modules', 'questions'])
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

    public function editPillar($pillarId)
    {
        $this->dispatch('edit-pillar', $pillarId);
    }

    public function openDeletePillarModal($request) {
        $pillar = Pillar::findOrFail($request['id']);

        $isUsed = $pillar->methodologies()->exists() || $pillar->modules()->exists() || $pillar->questions()->exists();

        if ($isUsed) {
            $this->dispatch('show-toast', type: 'error', message: __('messages.cannot_delete_pillar_used'));
            return;
        }

        $modal = [
            'title' => __('messages.delete_pillar_title'),
            'message' => __('messages.delete_pillar_message'),
            'note' => __('messages.delete_pillar_note'),
            'action' => __('messages.delete_action'),
            'callback' => 'deletePillar',
            'object' => $request
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function openPillarStatusModal($request) {
        $pillar = Pillar::findOrFail($request['id']);
        
        if($request['active']) {
            $title = __('messages.activate_pillar_title');
            $message = __('messages.activate_pillar_message');
            $action = __('messages.activate_action');
            $note = null;
        } else {
            // Check if pillar is in use
            $isUsed = $pillar->methodologies()->exists() || $pillar->modules()->exists() || $pillar->questions()->exists();
            
            if ($isUsed) {
                $this->dispatch('show-toast', type: 'error', message: __('messages.cannot_deactivate_pillar_used'));
                return;
            }
            
            $title = __('messages.deactivate_pillar_title');
            $message = __('messages.deactivate_pillar_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_pillar_note');
        }
        
        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changePillarStatus',
            'object' => $request
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changePillarStatus($request)
    {
        $pillar = Pillar::findOrFail($request['id']);
        $pillar->active = $request['active'];
        $pillar->save();
        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Pillar activated successfully!' : 'Pillar deactivated successfully!');
    }

    public function deletePillar($request)
    {
        $pillar = Pillar::findOrFail($request['id']);
        $pillar->delete();
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.homepage.tables.pillars-table', [
            'pillars' => $this->pillars,
        ]);
    }
}
