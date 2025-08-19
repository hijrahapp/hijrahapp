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

    // Pillar selection (for complex methodologies)
    public string $pillarSearch = '';
    public ?int $selectedPillarId = null;
    public array $pillarSuggestions = [];
    public bool $showPillarSuggestions = false;
    public bool $enablePillarSelection = false;

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

        // Enable pillar selection if methodology has linked pillars
        $this->enablePillarSelection = \DB::table('methodology_pillar')
            ->where('methodology_id', $this->methodologyId)
            ->exists();
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
            'selectedPillarId' => 'nullable|integer|exists:pillars,id',
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

    public function updatedPillarSearch(): void
    {
        if (!$this->enablePillarSelection) {
            $this->pillarSuggestions = [];
            $this->showPillarSuggestions = false;
            return;
        }
        if (strlen($this->pillarSearch) < 1) {
            $this->pillarSuggestions = [];
            $this->showPillarSuggestions = false;
            return;
        }

        $linkedPillarIds = \DB::table('methodology_pillar')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('pillar_id')
            ->toArray();

        $this->pillarSuggestions = \App\Models\Pillar::whereIn('id', $linkedPillarIds)
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

    public function updatedDependencySearch(): void
    {
        if (strlen($this->dependencySearch) < 1) {
            $this->dependencySuggestions = [];
            $this->showDependencySuggestions = false;
            return;
        }

        // In complex methodologies, dependencies should list modules linked to ANY pillar under this methodology.
        if ($this->enablePillarSelection) {
            $linkedModuleIds = \DB::table('pillar_module')
                ->where('methodology_id', $this->methodologyId)
                ->pluck('module_id')
                ->unique()
                ->toArray();
        } else {
            $linkedModuleIds = \DB::table('methodology_module')
                ->where('methodology_id', $this->methodologyId)
                ->pluck('module_id')
                ->unique()
                ->toArray();
        }

        $this->dependencySuggestions = Module::whereIn('id', $linkedModuleIds)
            ->when($this->selectedModuleId, function ($q) {
                $q->where('id', '!=', $this->selectedModuleId);
            })
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
                'selectedPillarId' => $this->enablePillarSelection ? 'required|integer|exists:pillars,id' : 'nullable|integer|exists:pillars,id',
            ]);
        } else {
            $this->validate($this->rules());
            if ($this->enablePillarSelection && !$this->selectedPillarId) {
                $this->dispatch('show-toast', type: 'error', message: 'Please select a pillar.');
                return;
            }
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
            if ($this->enablePillarSelection) {
                // Store edits on pillar_module for complex
                \DB::table('pillar_module')
                    ->where('methodology_id', $this->methodologyId)
                    ->where('module_id', $this->editingModuleId)
                    ->update([
                        'pillar_id' => $this->selectedPillarId,
                        'weight' => $this->weight !== '' ? (float)$this->weight : null,
                        'report' => $this->report !== '' ? $this->report : null,
                        'updated_at' => now(),
                    ]);
            } else {
                \DB::table('methodology_module')
                    ->where('methodology_id', $this->methodologyId)
                    ->where('module_id', $this->editingModuleId)
                    ->update([
                        'weight' => $this->weight !== '' ? (float)$this->weight : null,
                        'report' => $this->report !== '' ? $this->report : null,
                        'updated_at' => now(),
                    ]);
            }
            $this->dispatch('show-toast', type: 'success', message: 'Updated successfully');
        } else {
            // Attach module to methodology with pivot data
            if ($this->enablePillarSelection) {
                // Only link to pillar with all fields stored in pillar_module
                \DB::table('pillar_module')->updateOrInsert([
                    'methodology_id' => $this->methodologyId,
                    'pillar_id' => $this->selectedPillarId,
                    'module_id' => $this->selectedModuleId,
                ], [
                    'number_of_questions' => $this->numberOfQuestions !== '' ? (int)$this->numberOfQuestions : null,
                    'weight' => $this->weight !== '' ? (float)$this->weight : null,
                    'minutes' => $this->minutes !== '' ? (int)$this->minutes : null,
                    'report' => $this->report !== '' ? $this->report : null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            } else {
                // Simple methodology: store in methodology_module as before
                \DB::table('methodology_module')->updateOrInsert(
                    [
                        'methodology_id' => $this->methodologyId,
                        'module_id' => $this->selectedModuleId,
                    ],
                    [
                        'number_of_questions' => $this->numberOfQuestions !== '' ? (int)$this->numberOfQuestions : null,
                        'weight' => $this->weight !== '' ? (int)$this->weight : null,
                        'minutes' => (int)$this->minutes,
                        'report' => $this->report !== '' ? $this->report : null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

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
        $this->pillarSearch = '';
        $this->selectedPillarId = null;
        $this->pillarSuggestions = [];
        $this->showPillarSuggestions = false;
        $this->enablePillarSelection = false;
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

        // Enable pillar selection if methodology has linked pillars
        $this->enablePillarSelection = \DB::table('methodology_pillar')
            ->where('methodology_id', $this->methodologyId)
            ->exists();

        $module = Module::findOrFail($moduleId);
        $this->moduleName = $module->name;
        $this->selectedModuleId = $moduleId;
        $this->moduleSearch = $module->name;

        $pivot = \DB::table('methodology_module')
            ->where('methodology_id', $methodologyId)
            ->where('module_id', $moduleId)
            ->first();

        if ($this->enablePillarSelection) {
            $pm = \DB::table('pillar_module')
                ->where('methodology_id', $this->methodologyId)
                ->where('module_id', $this->editingModuleId)
                ->first();
            $this->numberOfQuestions = $pm && $pm->number_of_questions !== null ? (string)$pm->number_of_questions : '';
            $this->minutes = $pm && $pm->minutes !== null ? (string)$pm->minutes : '';
            $this->weight = $pm && $pm->weight !== null ? (float)$pm->weight : 0;
            $this->report = $pm && $pm->report ? (string)$pm->report : '';
        } else {
            $this->numberOfQuestions = $pivot && $pivot->number_of_questions !== null ? (string)$pivot->number_of_questions : '';
            $this->minutes = $pivot && $pivot->minutes !== null ? (string)$pivot->minutes : '';
            $this->weight = $pivot && $pivot->weight !== null ? (int)$pivot->weight : 0;
            $this->report = $pivot && $pivot->report ? (string)$pivot->report : '';
        }

        $this->dependencyIds = \DB::table('module_dependencies')
            ->where('methodology_id', $this->methodologyId)
            ->where('module_id', $this->editingModuleId)
            ->pluck('depends_on_module_id')
            ->toArray();

        // Load existing pillar link if any
        if ($this->enablePillarSelection) {
            $pm = \DB::table('pillar_module')
                ->where('methodology_id', $this->methodologyId)
                ->where('module_id', $this->editingModuleId)
                ->first();
            if ($pm) {
                $this->selectedPillarId = (int) $pm->pillar_id;
                $pillar = \App\Models\Pillar::find($this->selectedPillarId);
                $this->pillarSearch = $pillar?->name ?? '';
            }
        }
    }
}


