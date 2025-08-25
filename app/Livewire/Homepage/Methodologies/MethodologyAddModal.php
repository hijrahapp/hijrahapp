<?php

namespace App\Livewire\Homepage\Methodologies;

use App\Models\Methodology;
use Livewire\Component;

class MethodologyAddModal extends Component
{
    public string $name = '';

    public string $description = '';

    public string $definition = '';

    public string $objectives = '';

    public array $tags = [];

    public string $imgUrl = '';

    public string $type = '';

    public string $error = '';

    public bool $isEditMode = false;

    public ?int $methodologyId = null;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'definition' => 'required|string|min:3',
            'objectives' => 'nullable|string',

            'tags' => 'array',
            'tags.*' => 'integer',
            'imgUrl' => $this->isEditMode ? 'nullable|string' : 'required|string',
            'type' => 'required|in:simple,complex,twoSection',
        ];

        return $rules;
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-methodology' => 'editMethodology',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function editMethodology(int $methodologyId)
    {
        $this->isEditMode = true;
        $this->methodologyId = $methodologyId;

        $methodology = Methodology::findOrFail($methodologyId);
        $this->name = $methodology->name;
        $this->description = $methodology->description;
        $this->definition = $methodology->definition;
        $this->objectives = $methodology->objectives ?? '';
        $this->tags = $methodology->tags ?? [];
        $this->imgUrl = $methodology->img_url ?? '';
        $this->type = $methodology->type;
        $this->dispatch('show-modal', selector: '#methodology_add_modal');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->definition = '';
        $this->objectives = '';
        $this->tags = [];
        $this->imgUrl = '';
        $this->type = '';
        $this->error = '';
        $this->isEditMode = false;
        $this->methodologyId = null;
    }

    public function closeModal()
    {
        $this->dispatch('click');
        $this->resetForm();
    }

    public function save()
    {
        // Clear previous errors
        $this->resetErrorBag();

        try {
            $this->validate();

            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'definition' => $this->definition,
                'objectives' => $this->objectives,

                'tags' => $this->tags,
                'img_url' => $this->imgUrl ?: null,
                'type' => $this->type,
            ];

            if ($this->isEditMode) {
                $methodology = Methodology::findOrFail($this->methodologyId);
                $methodology->update($data);
            } else {
                $data['active'] = false; // Default status is deactivated
                $methodology = Methodology::create($data);
                $methodology->save();
            }

            $this->dispatch('refreshTable');
            $this->closeModal();
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Methodology updated successfully!' : 'Methodology added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Methodology save failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error = 'An error occurred while saving the methodology. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.homepage.methodologies.methodology-add-modal');
    }
}
