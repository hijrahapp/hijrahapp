<?php

namespace App\Livewire\Homepage\Liabilities\Modules;

use App\Models\Liability;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Traits\WithTableReload;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class LiabilityModulesTable extends Component
{
    use WithPagination, WithTableReload;

    public Liability $liability;

    public string $search = '';

    public ?int $selectedMethodologyId = null;

    public ?int $selectedPillarId = null;

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'moduleRemoved' => 'reloadTable',
    ];

    public function mount(Liability $liability)
    {
        $this->liability = $liability;
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
            $modules = $this->liability->modules();

            // Apply search filter
            if ($this->search) {
                $modules->where('modules.name', 'like', '%'.$this->search.'%');
            }

            // Apply methodology filter
            if ($this->selectedMethodologyId) {
                $modules->where('liability_module.methodology_id', $this->selectedMethodologyId);
            }

            // Apply pillar filter
            if ($this->selectedPillarId) {
                $modules->where('liability_module.pillar_id', $this->selectedPillarId);
            }

            $paginatedModules = $modules->orderBy('modules.name')->paginate($this->perPage);

            // Load methodology and pillar data for each module
            $methodologyIds = $paginatedModules->pluck('pivot.methodology_id')->filter()->unique();
            $pillarIds = $paginatedModules->pluck('pivot.pillar_id')->filter()->unique();

            $methodologies = \App\Models\Methodology::whereIn('id', $methodologyIds)->get()->keyBy('id');
            $pillars = \App\Models\Pillar::whereIn('id', $pillarIds)->get()->keyBy('id');

            // Attach methodology and pillar objects to each module
            foreach ($paginatedModules as $module) {
                $module->methodology = $methodologies->get($module->pivot->methodology_id);
                $module->pillar = $pillars->get($module->pivot->pillar_id);
            }

            return $paginatedModules;
        });
    }

    public function openAddModal()
    {
        $this->dispatch('openLiabilityModuleModal', $this->liability->id);
    }

    public function removeModule($moduleId, $methodologyId, $pillarId = null)
    {
        try {
            $query = $this->liability->modules()
                ->wherePivot('module_id', $moduleId)
                ->wherePivot('methodology_id', $methodologyId);

            if ($pillarId) {
                $query->wherePivot('pillar_id', $pillarId);
            } else {
                $query->whereNull('liability_module.pillar_id');
            }

            $query->detach($moduleId);

            session()->flash('success', 'Module removed from liability successfully.');
            $this->dispatch('refreshTable');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to remove module: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.modules.liability-modules-table', [
            'modules' => $this->modules,
            'methodologies' => $this->methodologies,
            'pillars' => $this->pillars,
        ]);
    }
}
