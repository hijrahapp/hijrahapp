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
    public $newTag = '';
    public $error = '';
    public $tagSuggestions = [];
    public $showTagSuggestions = false;
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

    public function updatedNewTag()
    {
        if (strlen($this->newTag) >= 2) {
            $this->tagSuggestions = Tag::where('title', 'like', '%' . $this->newTag . '%')
                ->where('active', true)
                ->limit(5)
                ->get(['id', 'title'])
                ->toArray();
            $this->showTagSuggestions = true;
        } else {
            $this->tagSuggestions = [];
            $this->showTagSuggestions = false;
        }
    }

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
        // Ensure tags are normalized to an array of integer IDs
        $this->tags = $this->normalizeTagIds($pillar->tags ?? []);

        $this->dispatch('show-modal', selector: '#pillar_add_modal');
    }

    public function selectTag($tagId, $tagTitle)
    {
        if (!in_array($tagId, $this->tags)) {
            $this->tags[] = $tagId;
        }
        $this->clearTagInput();
    }

    public function addTag()
    {
        if (empty($this->newTag)) {
            return;
        }

        // Check if tag already exists
        $existingTag = Tag::where('title', $this->newTag)->first();
        
        if ($existingTag) {
            if (!in_array($existingTag->id, $this->tags)) {
                $this->tags[] = $existingTag->id;
            }
        } else {
            // Create new tag
            $newTag = Tag::create([
                'title' => $this->newTag,
                'active' => true
            ]);
            $this->tags[] = $newTag->id;
        }

        $this->clearTagInput();
    }

    public function removeTag($tagId)
    {
        $this->tags = array_filter($this->tags, function($id) use ($tagId) {
            return $id != $tagId;
        });
    }

    public function clearTagInput()
    {
        $this->newTag = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
    }

    private function normalizeTagIds($tags)
    {
        if (is_string($tags)) {
            return json_decode($tags, true) ?? [];
        }
        
        if (is_array($tags)) {
            return array_map('intval', array_filter($tags, 'is_numeric'));
        }
        
        return [];
    }

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
        // Normalize tags to avoid validation errors if they were stored as objects/strings
        $this->tags = $this->normalizeTagIds($this->tags);
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
