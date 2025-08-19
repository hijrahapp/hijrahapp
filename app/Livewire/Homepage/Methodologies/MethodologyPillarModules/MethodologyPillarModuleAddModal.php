<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyPillarModules;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use Livewire\Component;

class MethodologyPillarModuleAddModal extends Component
{
    public int $methodologyId;

    public bool $isEditMode = false;
    public ?int $editingModuleId = null;
    public ?int $editingPillarId = null;
    public bool $isActiveMethodology = false;
    public string $moduleName = '';

    public string $moduleSearch = '';
    public ?int $selectedModuleId = null;
    public array $moduleSuggestions = [];
    public bool $showModuleSuggestions = false;

    public string $pillarSearch = '';
    public ?int $selectedPillarId = null;
    public array $pillarSuggestions = [];
    public bool $showPillarSuggestions = false;

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'open-add-methodology-pillar-module' => 'open',
        'open-edit-methodology-pillar-module' => 'openEdit',
    ];

    public function open(int $methodologyId): void
    {
        $this->resetForm();
        $this->methodologyId = $methodologyId;
        $methodology = Methodology::findOrFail($methodologyId);
        $this->isActiveMethodology = (bool) $methodology->active;
    }

    protected function rules(): array
    {
        return [
            'selectedModuleId' => 'required|integer|exists:modules,id',
            'selectedPillarId' => 'required|integer|exists:pillars,id',
        ];
    }

    public function updatedModuleSearch(): void
    {
        if (strlen($this->moduleSearch) < 1) {
            $this->moduleSuggestions = [];
            $this->showModuleSuggestions = false;
            return;
        }

        $alreadyLinkedModuleIds = \DB::table('pillar_module')
            ->where('methodology_id', $this->methodologyId)
            ->when($this->selectedPillarId, fn($q) => $q->where('pillar_id', $this->selectedPillarId))
            ->pluck('module_id')
            ->toArray();

        $this->moduleSuggestions = Module::where('name', 'like', "%{$this->moduleSearch}%")
            ->where('active', true)
            ->whereNotIn('id', $alreadyLinkedModuleIds)
            ->limit(7)
            ->get(['id', 'name'])
            ->toArray();
        $this->showModuleSuggestions = true;
    }

    public function selectModule(int $moduleId, string $moduleName): void
    {
        $this->selectedModuleId = $moduleId;
        $this->moduleSearch = $moduleName;
        $this->moduleSuggestions = [];
        $this->showModuleSuggestions = false;
    }

    public function updatedPillarSearch(): void
    {
        if (strlen($this->pillarSearch) < 1) {
            $this->pillarSuggestions = [];
            $this->showPillarSuggestions = false;
            return;
        }

        $linkedPillarIds = \DB::table('methodology_pillar')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('pillar_id')
            ->toArray();

        $this->pillarSuggestions = Pillar::whereIn('id', $linkedPillarIds)
            ->where('name', 'like', "%{$this->pillarSearch}%")
            ->limit(7)
            ->get(['id', 'name'])
            ->toArray();
        $this->showPillarSuggestions = true;
    }

    public function selectPillar(int $pillarId, string $pillarName): void
    {
        $this->selectedPillarId = $pillarId;
        $this->pillarSearch = $pillarName;
        $this->pillarSuggestions = [];
        $this->showPillarSuggestions = false;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->validate([
                'selectedPillarId' => 'required|integer|exists:pillars,id',
            ]);
        } else {
            $this->validate($this->rules());
        }

        if ($this->isActiveMethodology) {
            $this->dispatch('show-toast', type: 'error', message: 'This module exists in an active methodology');
            return;
        }

        if ($this->isEditMode && $this->editingModuleId && $this->editingPillarId) {
            // Move link to a different pillar (or keep same)
            \DB::table('pillar_module')
                ->where('methodology_id', $this->methodologyId)
                ->where('pillar_id', $this->editingPillarId)
                ->where('module_id', $this->editingModuleId)
                ->update([
                    'pillar_id' => $this->selectedPillarId,
                    'updated_at' => now(),
                ]);

            // If pillar changed, also move module questions to the new pillar context
            if ($this->editingPillarId !== $this->selectedPillarId) {
                \DB::table('module_question')
                    ->where('methodology_id', $this->methodologyId)
                    ->where('module_id', $this->editingModuleId)
                    ->where('pillar_id', $this->editingPillarId)
                    ->update([
                        'pillar_id' => $this->selectedPillarId,
                        'updated_at' => now(),
                    ]);
            }

            $this->dispatch('show-toast', type: 'success', message: 'Updated successfully');
        } else {
            // Link module to pillar under methodology
            \DB::table('pillar_module')->updateOrInsert(
                [
                    'methodology_id' => $this->methodologyId,
                    'pillar_id' => $this->selectedPillarId,
                    'module_id' => $this->selectedModuleId,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $this->dispatch('show-toast', type: 'success', message: 'Linked successfully');
        }

        $this->dispatch('refreshTable');
        $this->dispatch('click');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->resetErrorBag();
        $this->isEditMode = false;
        $this->editingModuleId = null;
        $this->editingPillarId = null;
        $this->moduleName = '';
        $this->moduleSearch = '';
        $this->selectedModuleId = null;
        $this->moduleSuggestions = [];
        $this->showModuleSuggestions = false;
        $this->pillarSearch = '';
        $this->selectedPillarId = null;
        $this->pillarSuggestions = [];
        $this->showPillarSuggestions = false;
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodologyPillarModules.methodology-pillar-module-add-modal', [
            'isEditMode' => $this->isEditMode,
        ]);
    }

    public function openEdit(int $methodologyId, int $moduleId, int $pillarId): void
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->methodologyId = $methodologyId;
        $this->editingModuleId = $moduleId;
        $this->editingPillarId = $pillarId;
        $methodology = Methodology::findOrFail($methodologyId);
        $this->isActiveMethodology = (bool) $methodology->active;

        $module = Module::findOrFail($moduleId);
        $this->moduleName = $module->name;
        $this->selectedModuleId = $moduleId;
        $this->moduleSearch = $module->name;

        $pillar = Pillar::findOrFail($pillarId);
        $this->selectedPillarId = $pillarId;
        $this->pillarSearch = $pillar->name;
    }
}




