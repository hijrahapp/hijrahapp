<?php

namespace App\Livewire\Homepage\Modals;

use App\Models\Pillar;
use App\Models\Tag;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PillarAddModal extends Component
{
    public $name = '';
    public $description = '';
    public $definition = '';
    public $objectives = '';
    public $tags = [];
    public $error = '';
    public $isEditMode = false;
    public $pillarId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:10',
            'definition' => 'required|string|min:10',
            'objectives' => 'required|string|min:10',
            'tags' => 'array',
            'tags.*' => 'required|integer',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Pillar name is required.',
            'name.min' => 'Pillar name must be at least 3 characters.',
            'description.required' => 'Description is required.',
            'description.min' => 'Description must be at least 10 characters.',
            'definition.required' => 'Definition is required.',
            'definition.min' => 'Definition must be at least 10 characters.',
            'objectives.required' => 'Objectives are required.',
            'objectives.min' => 'Objectives must be at least 10 characters.',
            'tags.*.required' => 'Tag is required.',
            'tags.*.integer' => 'Tag must be a valid ID.',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-pillar' => 'editPillar'
    ];

    public function mount()
    {
        $this->resetForm();
    }

    // Tag logic moved to shared TagPicker component

    public function editPillar($pillarId)
    {
        $this->isEditMode = true;
        $this->pillarId = $pillarId;
        
        $pillar = Pillar::findOrFail($pillarId);
        
        // Load pillar data
        $this->name = $pillar->name;
        $this->description = $pillar->description;
        $this->definition = $pillar->definition;
        $this->objectives = $pillar->objectives;
        // Tags are stored as an array of IDs on the model
        $this->tags = $pillar->tags ?? [];

        $this->dispatch('show-modal', selector: '#pillar_add_modal');
    }

    // Tag methods removed; component handles add/select/remove

    // Normalization not needed; TagPicker ensures numeric IDs

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->definition = '';
        $this->objectives = '';
        $this->tags = [];
        $this->newTag = '';
        $this->error = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
        $this->isEditMode = false;
        $this->pillarId = null;

        // Ensure frontend can re-hydrate editors with empty state
        $this->dispatch('morph.updated');
    }

    public function save()
    {
        // Tags are numeric IDs provided by TagPicker
        $this->validate();

        try {
            if ($this->isEditMode) {
                // Update existing pillar
                $pillar = Pillar::findOrFail($this->pillarId);
                $pillar->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'tags' => $this->tags,
                ]);
            } else {
                // Create new pillar
                Pillar::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'active' => true,
                    'tags' => $this->tags,
                ]);
            }

            $this->dispatch('refreshTable');
            $this->closeModal();
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Pillar updated successfully!' : 'Pillar created successfully!');
        
        } catch (\Exception $e) {
            $this->error = 'An error occurred while saving the pillar. Please try again.';
            logger()->error('Pillar save error: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->resetForm();
        // Follow existing pattern used by other modals
        $this->dispatch('click');
    }

    public function render()
    {
        return view('livewire.homepage.modals.pillar-add-modal');
    }
}
