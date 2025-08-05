<?php

namespace App\Livewire\Homepage\Modals;

use Livewire\Component;
use App\Models\Tag;

class TagAddModal extends Component
{
    public $titles = [];
    public $newTitle = '';
    public $error = '';
    public $duplicateErrors = [];

    protected $rules = [
        'titles' => 'required|array|min:1',
        'titles.*' => 'required|string|min:1',
    ];

    protected function messages()
    {
        return [
            'titles.required' => 'At least one tag is required.',
            'titles.min' => 'At least one tag is required.',
            'titles.*.required' => 'Tag title cannot be empty.',
            'titles.*.min' => 'Tag title must be at least 1 character.',
        ];
    }

    protected $listeners = ['reset-modal' => 'resetForm'];

    public function mount()
    {
    }

    public function closeModal()
    {
        $this->dispatch('click');
    }

    public function addTitle()
    {
        if (!empty(trim($this->newTitle))) {
            $title = trim($this->newTitle);
            if (!in_array($title, $this->titles)) {
                $this->titles[] = $title;
            }
            $this->newTitle = '';
        }
    }

    public function removeTitle($index)
    {
        if (isset($this->titles[$index])) {
            unset($this->titles[$index]);
            $this->titles = array_values($this->titles);
        }
    }

    public function saveTag()
    {
        $this->validate();
        
        // Check for duplicates in database
        $existingTags = Tag::whereIn('title', $this->titles)->pluck('title')->toArray();
        $duplicates = array_intersect($this->titles, $existingTags);
        
        if (!empty($duplicates)) {
            $this->duplicateErrors = $duplicates;
            $this->error = 'The following tags already exist: ' . implode(', ', $duplicates);
            return;
        }

        // Create new tags
        foreach ($this->titles as $title) {
            Tag::create([
                'title' => $title,
                'active' => true
            ]);
        }

        $this->dispatch('refreshTable');
        $this->closeModal();
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->titles = [];
        $this->newTitle = '';
        $this->error = '';
        $this->duplicateErrors = [];
    }

    public function render()
    {
        return view('livewire.homepage.modals.tag-add-modal');
    }
}
