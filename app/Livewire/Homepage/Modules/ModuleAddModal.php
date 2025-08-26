<?php

namespace App\Livewire\Homepage\Modules;

use App\Models\Module;
use Livewire\Component;

class ModuleAddModal extends Component
{
    public string $name = '';

    public string $description = '';

    public string $definition = '';

    public string $objectives = '';

    public array $tags = [];

    public string $imgUrl = '';

    public string $error = '';

    // Tag logic moved to TagPicker shared component
    public bool $isEditMode = false;

    public ?int $moduleId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3|max:200',
            'definition' => 'required|string|min:3',
            'objectives' => 'required|string|min:3',
            'tags' => 'array',
            'tags.*' => 'integer',
            'imgUrl' => 'nullable|string',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-module' => 'editModule',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    //

    public function editModule(int $moduleId)
    {
        $this->isEditMode = true;
        $this->moduleId = $moduleId;

        $module = Module::findOrFail($moduleId);
        $this->name = $module->name;
        $this->description = $module->description;
        $this->definition = $module->definition;
        $this->objectives = $module->objectives ?? '';
        // Tags already stored as array of IDs
        $this->tags = $module->tags ?? [];
        $this->imgUrl = $module->img_url ?? '';

        $this->dispatch('show-modal', selector: '#module_add_modal');
    }

    //

    // Normalization no longer needed; TagPicker ensures numeric ID array

    //

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->definition = '';
        $this->objectives = '';
        $this->tags = [];
        $this->imgUrl = '';
        $this->error = '';
        $this->isEditMode = false;
        $this->moduleId = null;
    }

    public function save()
    {
        // Clear previous errors
        $this->resetErrorBag();

        try {
            // Tags are numeric IDs
            $this->validate();

            // Ensure imgUrl is properly handled (could be base64 or HTTP URL)
            $imgUrlToSave = $this->imgUrl ?: null;
            if ($this->isEditMode) {
                $module = Module::findOrFail($this->moduleId);
                $module->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'tags' => $this->tags,
                    'img_url' => $imgUrlToSave,
                ]);
            } else {
                Module::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'tags' => $this->tags,
                    'img_url' => $imgUrlToSave,
                    'active' => true,
                ]);
            }
            $this->dispatch('refreshTable');
            $this->dispatch('click');
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Module updated successfully!' : 'Module created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they show in the form
            throw $e;
        } catch (\Exception $e) {
            $this->error = 'An error occurred while saving the module: '.$e->getMessage();
            logger()->error('Module save error: '.$e->getMessage(), [
                'module_data' => [
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'tags' => $this->tags,
                    'imgUrl' => $this->imgUrl ? substr($this->imgUrl, 0, 50).'...' : null,
                ],
            ]);
        }
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function render()
    {
        return view('livewire.homepage.modules.module-add-modal');
    }
}
