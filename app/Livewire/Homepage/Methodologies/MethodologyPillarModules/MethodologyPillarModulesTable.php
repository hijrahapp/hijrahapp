<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyPillarModules;

use App\Models\Module;
use App\Models\Pillar;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MethodologyPillarModulesTable extends Component
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
        'confirm-delete-methodology-pillar-module' => 'deleteMethodologyPillarModule',
    ];

    /**
     * Get names of modules that the given module depends on within the current methodology.
     *
     * @param int $moduleId
     * @return array<int, string>
     */
    public function getDependencyNames(int $moduleId): array
    {
        return \DB::table('module_dependencies as md')
            ->join('modules as m', 'm.id', '=', 'md.depends_on_module_id')
            ->where('md.methodology_id', $this->methodologyId)
            ->where('md.module_id', $moduleId)
            ->orderBy('md.id')
            ->pluck('m.name')
            ->toArray();
    }

    #[Computed]
    public function modules(): LengthAwarePaginator
    {
        $query = Module::query()
            ->where('modules.name', 'like', '%'.$this->search.'%')
            ->when($this->tagFilter, function ($q) {
                $q->whereJsonContains('modules.tags', (int) $this->tagFilter);
            })
            ->join('pillar_module as pm', function ($join) {
                $join->on('pm.module_id', '=', 'modules.id')
                    ->where('pm.methodology_id', '=', $this->methodologyId);
            })
            ->leftJoin('pillars as p', 'p.id', '=', 'pm.pillar_id')
            ->withCount(['questions'])
            ->orderBy('pm.created_at', 'asc')
            ->select('modules.*')
            ->selectRaw('pm.pillar_id as pm_pillar_id, p.name as pillar_name')
            ->selectRaw('(
                SELECT COUNT(*) FROM module_question mq
                WHERE mq.methodology_id = ? AND mq.module_id = modules.id AND mq.pillar_id = pm.pillar_id
            ) as mq_count', [$this->methodologyId]);

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
        $this->dispatch('open-add-methodology-pillar-module', methodologyId: $this->methodologyId);
        $this->dispatch('show-modal', selector: '#methodology_pillar_module_add_modal');
    }

    public function openEditModal(int $moduleId, int $pillarId): void
    {
        $this->dispatch('open-edit-methodology-pillar-module', methodologyId: $this->methodologyId, moduleId: $moduleId, pillarId: $pillarId);
        $this->dispatch('show-modal', selector: '#methodology_pillar_module_add_modal');
    }

    public function manageQuestions(int $moduleId, int $pillarId): void
    {
        $this->dispatch('open-manage-methodology-module-questions', methodologyId: $this->methodologyId, moduleId: $moduleId, pillarId: $pillarId);
        $this->dispatch('show-modal', selector: '#methodology_questions_modal');
    }

    public function openDeleteModal(int $moduleId, int $pillarId): void
    {
        $modal = [
            'title' => 'Unlink Module',
            'message' => 'This will unlink the module from the selected pillar. Module questions under this pillar will be removed. Continue?',
            'note' => null,
            'action' => 'Unlink',
            'callback' => 'confirm-delete-methodology-pillar-module',
            'object' => ['methodologyId' => $this->methodologyId, 'moduleId' => $moduleId, 'pillarId' => $pillarId],
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteMethodologyPillarModule($request): void
    {
        $methodologyId = (int)($request['methodologyId'] ?? 0);
        $moduleId = (int)($request['moduleId'] ?? 0);
        $pillarId = (int)($request['pillarId'] ?? 0);

        if (!$methodologyId || !$moduleId || !$pillarId) {
            return;
        }

        // Remove module questions under this pillar in this methodology
        \DB::table('module_question')
            ->where('methodology_id', $methodologyId)
            ->where('module_id', $moduleId)
            ->where('pillar_id', $pillarId)
            ->delete();

        // Unlink from pillar
        \DB::table('pillar_module')
            ->where('methodology_id', $methodologyId)
            ->where('pillar_id', $pillarId)
            ->where('module_id', $moduleId)
            ->delete();

        $this->dispatch('refreshTable');
        $this->dispatch('show-toast', type: 'success', message: 'Unlinked successfully');
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodologyPillarModules.methodology-pillar-modules-table', [
            'modules' => $this->modules,
        ]);
    }
}




