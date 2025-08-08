<?php

namespace App\Livewire\Homepage\Modals;

use App\Models\Module;
use App\Models\Tag;
use Livewire\Component;

class ModuleAddModal extends Component
{
    public string $name = '';
    public string $description = '';
    public string $definition = '';
    public string $objectives = '';
    public array $tags = [];
    public string $newTag = '';
    public string $error = '';
    public array $tagSuggestions = [];
    public bool $showTagSuggestions = false;
    public bool $isEditMode = false;
    public ?int $moduleId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'required|string|min:3',
            'definition' => 'required|string|min:3',
            'objectives' => 'required|string|min:3',
            'tags' => 'array',
            'tags.*' => 'required|integer',
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

    public function editModule(int $moduleId)
    {
        $this->isEditMode = true;
        $this->moduleId = $moduleId;

        $module = Module::findOrFail($moduleId);
        $this->name = $module->name;
        $this->description = $module->description;
        $this->definition = $module->definition;
        $this->objectives = $module->objectives ?? '';
        $this->tags = $this->normalizeTagIds($module->tags ?? []);

        $this->dispatch('show-modal', selector: '#module_add_modal');
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

        $existingTag = Tag::where('title', $this->newTag)->first();
        if ($existingTag) {
            if (!in_array($existingTag->id, $this->tags)) {
                $this->tags[] = $existingTag->id;
            }
        } else {
            $newTag = Tag::create(['title' => $this->newTag, 'active' => true]);
            $this->tags[] = $newTag->id;
        }

        $this->clearTagInput();
    }

    public function removeTag($tagId)
    {
        $this->tags = array_values(array_filter($this->tags, fn($id) => (int)$id !== (int)$tagId));
    }

    private function normalizeTagIds($tags): array
    {
        if (empty($tags)) return [];
        $ids = [];
        foreach ((array)$tags as $tag) {
            if (is_numeric($tag)) $ids[] = (int)$tag;
            elseif (is_array($tag) && isset($tag['id']) && is_numeric($tag['id'])) $ids[] = (int)$tag['id'];
        }
        return array_values(array_unique(array_filter($ids, fn($v)=>$v>0)));
    }

    public function clearTagInput()
    {
        $this->newTag = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
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
        $this->moduleId = null;
    }

    public function save()
    {
        $this->tags = $this->normalizeTagIds($this->tags);
        $this->validate();

        try {
            if ($this->isEditMode) {
                $module = Module::findOrFail($this->moduleId);
                $module->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'tags' => $this->tags,
                ]);
            } else {
                Module::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'definition' => $this->definition,
                    'objectives' => $this->objectives,
                    'tags' => $this->tags,
                ]);
            }
            $this->dispatch('refreshTable');
            $this->dispatch('click');
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Module updated successfully!' : 'Module created successfully!');
        } catch (\Exception $e) {
            $this->error = 'An error occurred while saving the module. Please try again.';
            logger()->error('Module save error: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('click');
    }

    public function render()
    {
        return view('livewire.homepage.modals.module-add-modal');
    }
}


