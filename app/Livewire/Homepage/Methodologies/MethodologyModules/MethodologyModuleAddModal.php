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

    public string $weight = '';

    // minutes removed; use questionsEstimatedTime instead
    public string $report = '';

    // Questions meta (pivot-level)
    public string $questionsDescription = '';

    public string $questionsEstimatedTime = '';

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

            'weight' => 'required|numeric|min:0|max:100',
            // minutes removed
            'report' => 'nullable|string',
            'questionsDescription' => 'nullable|string',
            'questionsEstimatedTime' => 'required|integer|min:0',
            'dependencyIds' => 'array',
            'dependencyIds.*' => 'integer|exists:modules,id',
            'selectedPillarId' => $this->enablePillarSelection
                ? 'required|integer|exists:pillars,id'
                : 'nullable|integer|exists:pillars,id',
        ];
    }

    public function updatedModuleSearch(): void
    {
        if (strlen($this->moduleSearch) < 1) {
            $this->moduleSuggestions = [];
            $this->showModuleSuggestions = false;

            return;
        }

        $excludedDirect = \DB::table('methodology_module')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('module_id')
            ->toArray();

        $excludedViaPillar = \DB::table('pillar_module')
            ->where('methodology_id', $this->methodologyId)
            ->pluck('module_id')
            ->toArray();

        $excludedIds = array_values(array_unique(array_merge($excludedDirect, $excludedViaPillar)));

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
        if (! $this->enablePillarSelection) {
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

    public function toggleDependency(int $moduleId, ?string $moduleName = null): void
    {
        if ($this->selectedModuleId && $moduleId === $this->selectedModuleId) {
            $this->dispatch('show-toast', type: 'error', message: 'A module cannot depend on itself.');

            return;
        }
        // Enforce single selection: select or clear
        if (in_array($moduleId, $this->dependencyIds, true)) {
            // Deselect if clicked again
            $this->dependencyIds = [];
            $this->dependencySearch = '';
        } else {
            $this->dependencyIds = [$moduleId];
            // Reflect selected dependency inside the input
            if ($moduleName !== null) {
                $this->dependencySearch = $moduleName;
            } else {
                $name = Module::find($moduleId)?->name;
                $this->dependencySearch = $name ?? '';
            }
        }

        // Hide suggestions after selection
        $this->dependencySuggestions = [];
        $this->showDependencySuggestions = false;
    }

    public function clearDependency(): void
    {
        $this->dependencyIds = [];
        $this->dependencySearch = '';
        $this->dependencySuggestions = [];
        $this->showDependencySuggestions = false;
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            $this->validate([
                'weight' => 'required|numeric|min:0|max:100',
                'report' => 'nullable|string',
                'questionsDescription' => 'nullable|string',
                'questionsEstimatedTime' => 'required|integer|min:0',
                'selectedPillarId' => $this->enablePillarSelection ? 'required|integer|exists:pillars,id' : 'nullable|integer|exists:pillars,id',
            ]);
        } else {
            $this->validate($this->rules());
            if ($this->enablePillarSelection && ! $this->selectedPillarId) {
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
            //            if ($this->isActiveMethodology) {
            //                $this->dispatch('show-toast', type: 'error', message: 'This module exists in an active methodology');
            //                return;
            //            }
            if ($this->enablePillarSelection) {
                // Store edits on pillar_module for complex
                \DB::table('pillar_module')
                    ->where('methodology_id', $this->methodologyId)
                    ->where('module_id', $this->editingModuleId)
                    ->update([
                        'pillar_id' => $this->selectedPillarId,
                        'weight' => $this->weight !== '' ? (float) $this->weight : null,
                        'report' => $this->report !== '' ? $this->report : null,
                        'questions_description' => $this->questionsDescription !== '' ? $this->questionsDescription : null,
                        'questions_estimated_time' => is_numeric($this->questionsEstimatedTime) ? (int) $this->questionsEstimatedTime : null,
                        'updated_at' => now(),
                    ]);
            } else {
                \DB::table('methodology_module')
                    ->where('methodology_id', $this->methodologyId)
                    ->where('module_id', $this->editingModuleId)
                    ->update([
                        'weight' => $this->weight !== '' ? (float) $this->weight : null,
                        'report' => $this->report !== '' ? $this->report : null,
                        'questions_description' => $this->questionsDescription !== '' ? $this->questionsDescription : null,
                        'questions_estimated_time' => is_numeric($this->questionsEstimatedTime) ? (int) $this->questionsEstimatedTime : null,
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
                    'weight' => $this->weight !== '' ? (float) $this->weight : null,
                    // minutes removed
                    'report' => $this->report !== '' ? $this->report : null,
                    'questions_description' => $this->questionsDescription !== '' ? $this->questionsDescription : null,
                    'questions_estimated_time' => is_numeric($this->questionsEstimatedTime) ? (int) $this->questionsEstimatedTime : null,
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
                        'weight' => $this->weight !== '' ? (int) $this->weight : null,
                        // minutes removed
                        'report' => $this->report !== '' ? $this->report : null,
                        'questions_description' => $this->questionsDescription !== '' ? $this->questionsDescription : null,
                        'questions_estimated_time' => is_numeric($this->questionsEstimatedTime) ? (int) $this->questionsEstimatedTime : null,
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

                $deps = array_unique(array_filter($this->dependencyIds, fn ($id) => $id !== $this->selectedModuleId));

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

        $this->weight = '';
        // minutes removed
        $this->report = '';
        $this->questionsDescription = '';
        $this->questionsEstimatedTime = '';
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
            $this->weight = $pm && $pm->weight !== null ? (float) $pm->weight : 0;
            $this->report = $pm && $pm->report ? (string) $pm->report : '';
            $this->questionsDescription = $pm && property_exists($pm, 'questions_description') && $pm->questions_description !== null ? (string) $pm->questions_description : '';
            $this->questionsEstimatedTime = $pm && property_exists($pm, 'questions_estimated_time') && is_numeric($pm->questions_estimated_time) ? (string) ((int) $pm->questions_estimated_time) : '';
        } else {
            $this->weight = $pivot && $pivot->weight !== null ? (int) $pivot->weight : 0;
            $this->report = $pivot && $pivot->report ? (string) $pivot->report : '';
            $this->questionsDescription = $pivot && property_exists($pivot, 'questions_description') && $pivot->questions_description !== null ? (string) $pivot->questions_description : '';
            $this->questionsEstimatedTime = $pivot && property_exists($pivot, 'questions_estimated_time') && is_numeric($pivot->questions_estimated_time) ? (string) ((int) $pivot->questions_estimated_time) : '';
        }

        $this->dependencyIds = \DB::table('module_dependencies')
            ->where('methodology_id', $this->methodologyId)
            ->where('module_id', $this->editingModuleId)
            ->pluck('depends_on_module_id')
            ->toArray();

        // Reflect any existing single dependency inside the input field in edit mode
        if (count($this->dependencyIds) > 0) {
            $firstId = $this->dependencyIds[0];
            $this->dependencySearch = Module::find($firstId)?->name ?? '';
        } else {
            $this->dependencySearch = '';
        }

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
