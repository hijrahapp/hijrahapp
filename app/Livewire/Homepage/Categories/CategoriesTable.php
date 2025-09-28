<?php

namespace App\Livewire\Homepage\Categories;

use App\Models\Category;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class CategoriesTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteCategory' => 'deleteCategory',
        'changeCategoryStatus' => 'changeCategoryStatus',
    ];

    #[Computed]
    public function categories()
    {
        return $this->handleReloadState(function () {
            $query = Category::where('name', 'like', '%'.$this->search.'%')
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    public function openCategoryStatusModal($request)
    {
        $category = Category::findOrFail($request['id']);

        if ($request['active']) {
            $title = 'Activate Category';
            $message = 'Are you sure you want to activate this category?';
            $action = 'Activate';
            $note = null;
        } else {
            $title = 'Deactivate Category';
            $message = 'Are you sure you want to deactivate this category?';
            $action = 'Deactivate';
            $note = 'This category will not be available for users.';
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeCategoryStatus',
            'object' => $request,
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeCategoryStatus($request)
    {
        $category = Category::findOrFail($request['id']);

        $category->active = $request['active'];
        $category->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Category activated successfully!' : 'Category deactivated successfully!');
    }

    public function openDeleteCategoryModal($request)
    {
        $modal = [
            'title' => 'Delete Category',
            'message' => 'Are you sure you want to delete this category?',
            'note' => 'This action cannot be undone.',
            'action' => 'Delete',
            'callback' => 'deleteCategory',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteCategory($categoryId)
    {
        try {
            $category = Category::find($categoryId);
            if ($category) {
                $category->delete();
                $this->dispatch('show-toast', type: 'success', message: 'Category deleted successfully.');
                $this->reloadTable();
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to delete category: '.$e->getMessage());
        }
    }

    public function editCategory($categoryId)
    {
        $this->dispatch('edit-category', $categoryId);
    }

    public function render()
    {
        return view('livewire.homepage.categories.categories-table');
    }
}
