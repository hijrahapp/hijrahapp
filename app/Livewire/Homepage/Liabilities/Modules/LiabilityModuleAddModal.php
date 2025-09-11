<?php

namespace App\Livewire\Homepage\Liabilities\Modules;

use App\Models\Liability;
use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class LiabilityModuleAddModal extends Component
{
    public ?int $liabilityId = null;

    // Filters
    public ?int $selectedMethodologyId = null;

    public ?int $selectedPillarId = null;

    public string $search = '';

    // Module selection
    public ?int $selectedModuleId = null;

    public string $error = '';

    protected $listeners = [
        'openLiabilityModuleModal' => 'openAddModal',
        'reset-liability-module-modal' => 'resetForm',
    ];

    protected function rules(): array
    {
        return [
            'selectedMethodologyId' => 'required|exists:methodology,id',
            'selectedModuleId' => 'required|exists:modules,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedMethodologyId.required' => 'Please select a methodology.',
            'selectedMethodologyId.exists' => 'The selected methodology is invalid.',
            'selectedModuleId.required' => 'Please select a module.',
            'selectedModuleId.exists' => 'The selected module is invalid.',
        ];
    }

    public function openAddModal($liabilityId)
    {
        $this->resetForm();
        $this->liabilityId = $liabilityId;
        $this->dispatch('show-modal', selector: '#liability_module_add_modal');
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

    public function getAvailableModulesProperty()
    {
        if (! $this->selectedMethodologyId) {
            return collect();
        }

        $liability = Liability::find($this->liabilityId);
        if (! $liability) {
            return collect();
        }

        // Get modules that are available for the selected methodology
        $query = Module::where('active', true);

        // Filter by methodology and pillar selection
        if ($this->selectedPillarId) {
            // If pillar is selected, only show modules that belong to this specific pillar
            $query->whereHas('pillars', function (Builder $pillarQuery) {
                $pillarQuery->where('pillar_module.pillar_id', $this->selectedPillarId)
                    ->where('pillar_module.methodology_id', $this->selectedMethodologyId);
            });
        } else {
            // If no pillar selected, show modules that are either:
            // 1. Directly attached to the methodology, OR
            // 2. Attached to any pillar within the methodology
            $query->where(function (Builder $q) {
                // Direct methodology modules
                $q->whereHas('methodologies', function (Builder $methodologyQuery) {
                    $methodologyQuery->where('methodology.id', $this->selectedMethodologyId);
                });

                // OR any pillar modules within the methodology
                $q->orWhereHas('pillars', function (Builder $pillarQuery) {
                    $pillarQuery->where('pillar_module.methodology_id', $this->selectedMethodologyId);
                });
            });
        }

        // Exclude modules already assigned to this liability
        $existingModuleIds = $liability->modules()
            ->where('liability_module.methodology_id', $this->selectedMethodologyId)
            ->when($this->selectedPillarId, function ($q) {
                $q->where('liability_module.pillar_id', $this->selectedPillarId);
            })
            ->pluck('modules.id');

        $query->whereNotIn('id', $existingModuleIds);

        // Apply search filter
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->orderBy('name')->get();
    }

    public function updatedSelectedMethodologyId()
    {
        $this->selectedPillarId = null;
        $this->selectedModuleId = null;
    }

    public function updatedSelectedPillarId()
    {
        $this->selectedModuleId = null;
    }

    public function selectModule($moduleId)
    {
        $this->selectedModuleId = $moduleId;
    }

    public function addModule()
    {
        $this->validate();

        try {
            $liability = Liability::find($this->liabilityId);

            // Check if this exact combination already exists
            $exists = $liability->modules()
                ->where('modules.id', $this->selectedModuleId)
                ->where('liability_module.methodology_id', $this->selectedMethodologyId)
                ->where('liability_module.pillar_id', $this->selectedPillarId)
                ->exists();

            if ($exists) {
                $this->error = 'This module is already assigned to this liability with the same methodology and pillar.';

                return;
            }

            // Attach the module with the pivot data
            $liability->modules()->attach($this->selectedModuleId, [
                'methodology_id' => $this->selectedMethodologyId,
                'pillar_id' => $this->selectedPillarId,
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Module added successfully!',
            ]);

            $this->dispatch('refreshTable');
            $this->closeModal();

        } catch (\Exception $e) {
            $this->error = 'Failed to add module: '.$e->getMessage();
        }
    }

    public function resetForm()
    {
        $this->reset([
            'liabilityId', 'selectedMethodologyId', 'selectedPillarId',
            'search', 'selectedModuleId', 'error',
        ]);
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function render()
    {
        return view('livewire.homepage.liabilities.modules.liability-module-add-modal', [
            'methodologies' => $this->methodologies,
            'pillars' => $this->pillars,
            'availableModules' => $this->availableModules,
        ]);
    }
}
