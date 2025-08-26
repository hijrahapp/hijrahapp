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
    use \App\Traits\HasTagTitles, WithoutUrlPagination;

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

    /**
     * Get names of modules that the given module depends on within the current methodology.
     *
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
        $hasPillars = \DB::table('methodology_pillar')
            ->where('methodology_id', $this->methodologyId)
            ->exists();

        if ($hasPillars) {
            $hasWeight = \Schema::hasColumn('pillar_module', 'weight');
            $hasReport = \Schema::hasColumn('pillar_module', 'report');

            $selectPieces = [];
            $selectPieces[] = ($hasWeight ? 'pm.weight' : 'NULL').' as mm_weight';
            $selectPieces[] = ($hasReport ? 'pm.report' : 'NULL').' as mm_reports';
            $selectPieces[] = 'pm.pillar_id as pm_pillar_id';
            $selectPieces[] = 'p.name as pillar_name';
            $selectRaw = implode(', ', $selectPieces);

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
                ->selectRaw($selectRaw);
        } else {
            $hasWeight = \Schema::hasColumn('methodology_module', 'weight');
            $hasReport = \Schema::hasColumn('methodology_module', 'report');

            $selectPieces = [];
            $selectPieces[] = ($hasWeight ? 'mm.weight' : 'NULL').' as mm_weight';
            // minutes removed column safely
            $selectPieces[] = ($hasReport ? 'mm.report' : 'NULL').' as mm_reports';
            $selectRaw = implode(', ', $selectPieces);

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
                ->select('modules.*')
                ->selectRaw($selectRaw);
        }

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
        $pillarId = null;
        $hasPillars = \DB::table('methodology_pillar')
            ->where('methodology_id', $this->methodologyId)
            ->exists();
        if ($hasPillars) {
            $pillarId = \DB::table('pillar_module')
                ->where('methodology_id', $this->methodologyId)
                ->where('module_id', $moduleId)
                ->value('pillar_id');
        }
        $this->dispatch('open-manage-methodology-module-questions', methodologyId: $this->methodologyId, moduleId: $moduleId, pillarId: $pillarId);
        $this->dispatch('show-modal', selector: '#methodology_questions_modal');
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
        $methodologyId = (int) ($request['methodologyId'] ?? 0);
        $moduleId = (int) ($request['moduleId'] ?? 0);

        if (! $methodologyId || ! $moduleId) {
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

        // Remove pillar linkage if exists for this module under this methodology
        if (\Schema::hasTable('pillar_module')) {
            \DB::table('pillar_module')
                ->where('methodology_id', $methodologyId)
                ->where('module_id', $moduleId)
                ->delete();
        }

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
