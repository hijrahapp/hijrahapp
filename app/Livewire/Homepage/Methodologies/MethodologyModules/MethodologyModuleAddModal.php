<?php

namespace App\Livewire\Homepage\Methodologies\MethodologyModules;

use App\Models\Methodology;
use App\Models\Module;
use Livewire\Component;

class MethodologyModuleAddModal extends Component
{
    public int $methodologyId;

    public bool $isEditMode = false;
    public ?int $editingModuleId = null;
    public bool $isActiveMethodology = false;
    public string $moduleName = '';

    public string $moduleSearch = '';
    public ?int $selectedModuleId = null;
    public array $moduleSuggestions = [];
    public bool $showModuleSuggestions = false;

    public string $numberOfQuestions = '';
    public string $weight = '';
    public string $minutes = '';
    public string $report = '';

    public array $dependencyIds = [];
    public array $dependencySuggestions = [];
    public string $dependencySearch = '';
    public bool $showDependencySuggestions = false;

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'open-add-methodology-module' => 'open',
        'open-edit-methodology-module' => 'openEdit',
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
        // Default add rules
        return [
            'selectedModuleId' => 'required|integer|exists:modules,id',
            'numberOfQuestions' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric',
            'minutes' => 'required|integer|min:1',
            'report' => 'nullable|string',
            'dependencyIds' => 'array',
            'dependencyIds.*' => 'integer|exists:modules,id',
        ];
    }

    public function updatedModuleSearch(): void
    {
        if (strlen($this->moduleSearch) < 1) {
            $this->moduleSuggestions = [];
            $this->showModuleSuggestions = false;
            return;
        }

        $excludedIds = \DB::table('methodology_module')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('module_id')
            ->toArray();

        $this->moduleSuggestions = Module::where('name', 'like', "%{$this->moduleSearch}%")
            ->where('active', true)
            ->whereNotIn('id', $excludedIds)
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
        // Ensure selected module is not in dependencies
        $this->dependencyIds = array_values(array_diff($this->dependencyIds, [$moduleId]));
    }

    public function updatedDependencySearch(): void
    {
        if (strlen($this->dependencySearch) < 1) {
            $this->dependencySuggestions = [];
            $this->showDependencySuggestions = false;
            return;
        }

        $linkedModuleIds = \DB::table('methodology_module')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('module_id')
            ->toArray();

        $this->dependencySuggestions = Module::whereIn('id', $linkedModuleIds)
            ->where('name', 'like', "%{$this->dependencySearch}%")
            ->limit(7)
            ->get(['id', 'name'])
            ->toArray();
        $this->showDependencySuggestions = true;
    }

    public function toggleDependency(int $moduleId): void
    {
        if ($this->selectedModuleId && $moduleId === $this->selectedModuleId) {
            $this->dispatch('show-toast', type: 'error', message: 'A module cannot depend on itself.');
            return;
        }
        if (in_array($moduleId, $this->dependencyIds, true)) {
            $this->dependencyIds = array_values(array_diff($this->dependencyIds, [$moduleId]));
        } else {
            $this->dependencyIds[] = $moduleId;
        }

        // Clear search and hide suggestions after each selection
        $this->dependencySearch = '';
        $this->dependencySuggestions = [];
        $this->showDependencySuggestions = false;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->validate([
                'weight' => 'nullable|numeric',
                'report' => 'nullable|string',
            ]);
        } else {
            $this->validate($this->rules());
        }

        // Prevent circular dependencies
        if ($this->selectedModuleId && in_array($this->selectedModuleId, $this->dependencyIds, true)) {
            $this->dispatch('show-toast', type: 'error', message: 'A module cannot depend on itself.');
            return;
        }

        if ($this->isEditMode && $this->editingModuleId) {
            // Update only allowed fields on edit
            if ($this->isActiveMethodology) {
                $this->dispatch('show-toast', type: 'error', message: 'This module exists in an active methodology');
                return;
            }
            \DB::table('methodology_module')
                ->where('methodology_id', $this->methodologyId)
                ->where('module_id', $this->editingModuleId)
                ->update([
                    'weight' => $this->weight !== '' ? (float)$this->weight : null,
                    'report' => $this->report !== '' ? $this->report : null,
                    'updated_at' => now(),
                ]);
            $this->dispatch('show-toast', type: 'success', message: 'Updated successfully');
        } else {
            // Attach module to methodology with pivot data
            \DB::table('methodology_module')->updateOrInsert(
                [
                    'methodology_id' => $this->methodologyId,
                    'module_id' => $this->selectedModuleId,
                ],
                [
                    'number_of_questions' => $this->numberOfQuestions !== '' ? (int)$this->numberOfQuestions : null,
                    'weight' => $this->weight !== '' ? (float)$this->weight : null,
                    'minutes' => (int)$this->minutes,
                    'report' => $this->report !== '' ? $this->report : null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Save dependencies
            if ($this->selectedModuleId) {
                // First, clear existing deps for this module
                \DB::table('module_dependencies')
                    ->where('methodology_id', $this->methodologyId)
                    ->where('module_id', $this->selectedModuleId)
                    ->delete();

                $deps = array_unique(array_filter($this->dependencyIds, fn($id) => $id !== $this->selectedModuleId));

                foreach ($deps as $dependsOnId) {
                    \DB::table('module_dependencies')->insert([
                        'methodology_id' => $this->methodologyId,
                        'module_id' => $this->selectedModuleId,
                        'depends_on_module_id' => $dependsOnId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->dispatch('show-toast', type: 'success', message: 'Added successfully');
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
        $this->moduleName = '';
        $this->moduleSearch = '';
        $this->selectedModuleId = null;
        $this->moduleSuggestions = [];
        $this->showModuleSuggestions = false;
        $this->numberOfQuestions = '';
        $this->weight = '';
        $this->minutes = '';
        $this->report = '';
        $this->dependencyIds = [];
        $this->dependencySuggestions = [];
        $this->dependencySearch = '';
        $this->showDependencySuggestions = false;
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function render()
    {
        $selectedDependencyNames = [];
        if (count($this->dependencyIds) > 0) {
            $selectedDependencyNames = Module::whereIn('id', $this->dependencyIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        return view('livewire.homepage.methodologies.methodologyModules.methodology-module-add-modal', [
            'selectedDependencyNames' => $selectedDependencyNames,
            'isEditMode' => $this->isEditMode,
        ]);
    }

    public function openEdit(int $methodologyId, int $moduleId): void
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->methodologyId = $methodologyId;
        $this->editingModuleId = $moduleId;
        $methodology = Methodology::findOrFail($methodologyId);
        $this->isActiveMethodology = (bool) $methodology->active;

        $module = Module::findOrFail($moduleId);
        $this->moduleName = $module->name;
        $this->selectedModuleId = $moduleId;
        $this->moduleSearch = $module->name;

        $pivot = \DB::table('methodology_module')
            ->where('methodology_id', $methodologyId)
            ->where('module_id', $moduleId)
            ->first();

        $this->numberOfQuestions = $pivot && $pivot->number_of_questions !== null ? (string)$pivot->number_of_questions : '';
        $this->minutes = $pivot && $pivot->minutes !== null ? (string)$pivot->minutes : '';
        $this->weight = $pivot && $pivot->weight !== null ? (string)$pivot->weight : '';
        $this->report = $pivot && $pivot->report ? (string)$pivot->report : '';

        $this->dependencyIds = \DB::table('module_dependencies')
            ->where('methodology_id', $this->methodologyId)
            ->where('module_id', $this->editingModuleId)
            ->pluck('depends_on_module_id')
            ->toArray();
    }
}


