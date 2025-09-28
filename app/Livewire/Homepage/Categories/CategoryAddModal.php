<?php

namespace App\Livewire\Homepage\Categories;

use App\Models\Category;
use Livewire\Component;

class CategoryAddModal extends Component
{
    public string $name = '';

    public string $icon = '';

    public string $error = '';

    public bool $isEditMode = false;

    public ?int $categoryId = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'icon' => 'required|string|url|max:500',
        ];
    }

    protected $listeners = [
        'reset-modal' => 'resetForm',
        'edit-category' => 'editCategory',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->icon = '';
        $this->error = '';
        $this->isEditMode = false;
        $this->categoryId = null;
        $this->resetValidation();
    }

    public function editCategory($categoryId)
    {
        $category = Category::find($categoryId);
        if ($category) {
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->icon = $category->icon;
            $this->isEditMode = true;
        }
    }

    public function save()
    {
        try {
            $this->error = '';
            $this->validate();

            $data = [
                'name' => $this->name,
                'icon' => $this->icon,
                'active' => false,
            ];

            if ($this->isEditMode && $this->categoryId) {
                $category = Category::find($this->categoryId);
                if ($category) {
                    // Keep the original active status when editing
                    unset($data['active']);
                    $category->update($data);
                }
            } else {
                Category::create($data);
            }

            $this->dispatch('refreshTable');
            $this->closeModal();
            $this->dispatch('show-toast', type: 'success', message: $this->isEditMode ? 'Category updated successfully.' : 'Category created successfully.');
        } catch (\Exception $e) {
            $this->error = 'Failed to save category: '.$e->getMessage();
        }
    }

    public function saveAndAddNew()
    {
        try {
            $this->error = '';
            $this->validate();

            $data = [
                'name' => $this->name,
                'icon' => $this->icon,
                'active' => false,
            ];

            Category::create($data);

            $this->dispatch('refreshTable');
            $this->dispatch('show-toast', type: 'success', message: 'Category created successfully.');

            // Reset form but keep modal open
            $this->name = '';
            $this->icon = '';
            $this->resetValidation();
        } catch (\Exception $e) {
            $this->error = 'Failed to save category: '.$e->getMessage();
        }
    }

    public function closeModal()
    {
        $this->dispatch('click');
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.homepage.categories.category-add-modal');
    }
}
