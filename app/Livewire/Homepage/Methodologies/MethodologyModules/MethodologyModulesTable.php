<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyModules;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MethodologyModulesTable extends Component
{
    use WithoutUrlPagination, \App\Traits\HasTagTitles;

    public int $methodologyId;

    public string $search = '';
    public int $perPage = 10;
    public string $tagFilter = '';
    public string $tagSearch = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;

    protected $listeners = [
        'refreshTable' => '$refresh',
        'confirm-delete-methodology-module' => 'deleteMethodologyModule',
    ];

    #[Computed]
    public function modules(): LengthAwarePaginator
    {
        $query = Module::query()
            ->where('modules.name', 'like', '%'.$this->search.'%')
            ->whereHas('methodologies', function ($q) {
                $q->where('methodology_id', $this->methodologyId);
            })
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('modules.tags', (int) $this->tagFilter);
            })
            ->join('methodology_module as mm', function ($join) {
                $join->on('mm.module_id', '=', 'modules.id')
                    ->where('mm.methodology_id', '=', $this->methodologyId);
            })
            ->withCount(['questions'])
            ->orderBy('mm.created_at', 'asc')
            ->select('modules.*');

        $page = $this->getPage();
        return $query->paginate($this->perPage, ['*'], 'page', $page);
    }

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
        $this->showTagSuggestions = false;
        $this->tagSearch = '';
    }

    public function openAddModal(): void
    {
        $this->dispatch('open-add-methodology-module', methodologyId: $this->methodologyId);
        $this->dispatch('show-modal', selector: '#methodology_module_add_modal');
    }

    public function openEditModal(int $moduleId): void
    {
        $this->dispatch('open-edit-methodology-module', methodologyId: $this->methodologyId, moduleId: $moduleId);
        $this->dispatch('show-modal', selector: '#methodology_module_add_modal');
    }

    public function manageQuestions(int $moduleId): void
    {
        $this->dispatch('open-manage-methodology-module-questions', methodologyId: $this->methodologyId, moduleId: $moduleId);
        $this->dispatch('show-modal', selector: '#methodology_module_questions_modal');
    }

    public function openDeleteModal(int $moduleId): void
    {
        // If other modules depend on this module, block before confirmation
        $hasDependents = \DB::table('module_dependencies')
            ->where('methodology_id', $this->methodologyId)
            ->where('depends_on_module_id', $moduleId)
            ->exists();

        if ($hasDependents) {
            $this->dispatch('show-toast', type: 'error', message: "Can't remove this module; remove dependent modules first.");
            return;
        }

        $modal = [
            'title' => 'Remove Module',
            'message' => 'Removing this module will remove all associated questions. Are you sure?',
            'note' => null,
            'action' => 'Remove',
            'callback' => 'confirm-delete-methodology-module',
            'object' => ['methodologyId' => $this->methodologyId, 'moduleId' => $moduleId],
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteMethodologyModule($request): void
    {
        $methodologyId = (int)($request['methodologyId'] ?? 0);
        $moduleId = (int)($request['moduleId'] ?? 0);

        if (!$methodologyId || !$moduleId) {
            return;
        }

        // Prevent deletion if other modules depend on this one
        $hasDependents = \DB::table('module_dependencies')
            ->where('methodology_id', $methodologyId)
            ->where('depends_on_module_id', $moduleId)
            ->exists();

        if ($hasDependents) {
            $this->dispatch('show-toast', type: 'error', message: "Can't remove this module; remove dependent modules first.");
            return;
        }

        // Remove dependencies for this module in this methodology
        \DB::table('module_dependencies')
            ->where('methodology_id', $methodologyId)
            ->where(function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId)->orWhere('depends_on_module_id', $moduleId);
            })
            ->delete();

        // Remove module questions placed directly under methodology for this module
        \DB::table('module_question')
            ->where('methodology_id', $methodologyId)
            ->where('module_id', $moduleId)
            ->delete();

        // Detach from methodology
        \DB::table('methodology_module')
            ->where('methodology_id', $methodologyId)
            ->where('module_id', $moduleId)
            ->delete();

        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: 'Removed successfully');
    }

    public function render()
    {
        $methodology = Methodology::find($this->methodologyId);
        return view('livewire.homepage.methodologies.methodologyModules.methodology-modules-table', [
            'modules' => $this->modules,
            'methodology' => $methodology,
        ]);
    }
}


