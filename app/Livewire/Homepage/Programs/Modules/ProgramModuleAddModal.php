<?php

namespace App\Livewire\Homepage\Programs\Modules;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ProgramModuleAddModal extends Component
{
    public ?int $programId = null;

    // Filters
    public ?int $selectedMethodologyId = null;

    public ?int $selectedPillarId = null;

    public string $search = '';

    // Module selection and configuration
    public ?int $selectedModuleId = null;

    public ?float $minScore = null;

    public ?float $maxScore = null;

    public string $error = '';

    protected $listeners = [
        'open-add-module-modal' => 'openAddModal',
        'reset-module-modal' => 'resetForm',
    ];

    protected function rules(): array
    {
        return [
            'selectedMethodologyId' => 'required|exists:methodology,id',
            'selectedModuleId' => 'required|exists:modules,id',
            'minScore' => 'required|numeric|min:0|max:100',
            'maxScore' => 'required|numeric|min:0|max:100|gte:minScore',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedMethodologyId.required' => 'Please select a methodology.',
            'selectedMethodologyId.exists' => 'The selected methodology is invalid.',
            'selectedModuleId.required' => 'Please select a module.',
            'selectedModuleId.exists' => 'The selected module is invalid.',
            'minScore.required' => 'Minimum score is required.',
            'minScore.numeric' => 'Minimum score must be a number.',
            'minScore.min' => 'Minimum score must be at least 0.',
            'minScore.max' => 'Minimum score must not exceed 100.',
            'maxScore.required' => 'Maximum score is required.',
            'maxScore.numeric' => 'Maximum score must be a number.',
            'maxScore.min' => 'Maximum score must be at least 0.',
            'maxScore.max' => 'Maximum score must not exceed 100.',
            'maxScore.gte' => 'Maximum score must be greater than or equal to minimum score.',
        ];
    }

    public function openAddModal($programId)
    {
        $this->resetForm();
        $this->programId = $programId;
        $this->dispatch('show-modal', selector: '#program_module_add_modal');
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

        $program = Program::find($this->programId);
        if (! $program) {
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

        // Exclude modules already assigned to this program
        $existingModuleIds = $program->modules()
            ->where('program_module.methodology_id', $this->selectedMethodologyId)
            ->when($this->selectedPillarId, function ($q) {
                $q->where('program_module.pillar_id', $this->selectedPillarId);
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
            $program = Program::find($this->programId);

            // Check if this exact combination already exists
            $exists = $program->modules()
                ->where('modules.id', $this->selectedModuleId)
                ->where('program_module.methodology_id', $this->selectedMethodologyId)
                ->where('program_module.pillar_id', $this->selectedPillarId)
                ->exists();

            if ($exists) {
                $this->error = 'This module is already assigned to this program with the same methodology and pillar.';

                return;
            }

            // Attach the module with the pivot data
            $program->modules()->attach($this->selectedModuleId, [
                'methodology_id' => $this->selectedMethodologyId,
                'pillar_id' => $this->selectedPillarId,
                'min_score' => $this->minScore,
                'max_score' => $this->maxScore,
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
            'programId', 'selectedMethodologyId', 'selectedPillarId',
            'search', 'selectedModuleId', 'minScore', 'maxScore', 'error',
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
        return view('livewire.homepage.programs.modules.program-module-add-modal', [
            'methodologies' => $this->methodologies,
            'pillars' => $this->pillars,
            'availableModules' => $this->availableModules,
        ]);
    }
}
