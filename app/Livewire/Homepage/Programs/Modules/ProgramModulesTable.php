<?php

namespace App\Livewire\Homepage\Programs\Modules;

use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\Program;
use App\Traits\WithTableReload;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramModulesTable extends Component
{
    use WithPagination, WithTableReload;

    public Program $program;

    public string $search = '';

    public ?int $selectedMethodologyId = null;

    public ?int $selectedPillarId = null;

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'removeModule' => 'removeModule',
    ];

    public function mount(Program $program)
    {
        $this->program = $program;
    }

    public function getMethodologiesProperty()
    {
        return Methodology::where('active', true)->orderBy('name')->get();
    }

    public function getPillarsProperty()
    {
        if (! $this->selectedMethodologyId) {
            return collect();
        }

        return Pillar::whereHas('methodologies', function (Builder $query) {
            $query->where('methodology.id', $this->selectedMethodologyId);
        })->orderBy('name')->get();
    }

    public function getModulesProperty()
    {
        return $this->handleReloadState(function () {
            $modules = $this->program->modules();

            // Apply search filter
            if ($this->search) {
                $modules->where('modules.name', 'like', '%'.$this->search.'%');
            }

            // Apply methodology filter
            if ($this->selectedMethodologyId) {
                $modules->where('program_module.methodology_id', $this->selectedMethodologyId);
            }

            // Apply pillar filter
            if ($this->selectedPillarId) {
                $modules->where('program_module.pillar_id', $this->selectedPillarId);
            }

            $paginatedModules = $modules->orderBy('modules.name')->paginate($this->perPage);

            // Load methodology and pillar data for each module
            $methodologyIds = $paginatedModules->pluck('pivot.methodology_id')->filter()->unique();
            $pillarIds = $paginatedModules->pluck('pivot.pillar_id')->filter()->unique();

            $methodologies = \App\Models\Methodology::whereIn('id', $methodologyIds)->get()->keyBy('id');
            $pillars = \App\Models\Pillar::whereIn('id', $pillarIds)->get()->keyBy('id');

            // Attach methodology and pillar data to each module
            foreach ($paginatedModules as $module) {
                $module->methodology = $methodologies->get($module->pivot->methodology_id);
                $module->pillar = $pillars->get($module->pivot->pillar_id);
            }

            return $paginatedModules;
        });
    }

    public function updatedSelectedMethodologyId()
    {
        $this->selectedPillarId = null;
        $this->resetPageAndReload();
    }

    public function updatedSelectedPillarId()
    {
        $this->resetPageAndReload();
    }

    public function openAddModal()
    {
        $this->dispatch('open-add-module-modal', programId: $this->program->id);
    }

    public function openRemoveModuleModal($request)
    {
        $modal = [
            'title' => __('messages.remove_module_title'),
            'message' => __('messages.remove_module_message'),
            'note' => __('messages.remove_module_note'),
            'action' => __('messages.remove_module_action'),
            'callback' => 'removeModule',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function removeModule($moduleId)
    {
        try {
            $this->program->modules()->detach($moduleId);

            $this->dispatch('show-toast', type: 'success', message: 'Module removed successfully.');

            $this->reloadTable();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to remove module: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.modules.program-modules-table', [
            'modules' => $this->modules,
            'methodologies' => $this->methodologies,
            'pillars' => $this->pillars,
        ]);
    }
}
