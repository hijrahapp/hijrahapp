<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Tag;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TagsTable extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshTable' => '$refresh',
        'changeTagStatus' => 'changeTagStatus',
        'deleteTag' => 'deleteTag',
    ];

    #[Computed]
    public function tags()
    {
        $query = Tag::where('title', 'like', '%'.$this->search.'%')
            ->orderBy('id', 'asc');
        return $query->paginate($this->perPage);
    }

    public function openTagStatusModal($request) {
        if($request['active']) {
            $title = 'Activate Tag';
            $message = 'Are you sure you want to activate this tag?';
            $action = 'Activate';
        } else {
            $title = 'Deactivate Tag';
            $message = 'Are you sure you want to deactivate this tag?';
            $action = 'Deactivate';
        }
        $callback = 'changeTagStatus';
        $this->dispatch('openConfirmationModal', $title, $message, $action, $callback, $request);
    }

    public function openTagDeleteModal($request) {
        $title = 'Delete Tag';
        $message = 'Are you sure you want to delete this tag?';
        $action = 'Delete';
        $callback = 'deleteTag';
        $this->dispatch('openConfirmationModal', $title, $message, $action, $callback, $request);
    }

    public function changeTagStatus($request)
    {
        $tag = Tag::findOrFail($request['id']);
        $tag->active = $request['active'];
        $tag->save();
        $this->dispatch('refreshTable');
    }

    public function deleteTag($request)
    {
        $tag = Tag::findOrFail($request['id']);
        $tag->delete();
        $this->dispatch('refreshTable');
    }

    public function getSearchProperty()
    {
        return $this->search;
    }

    public function setSearchProperty($value)
    {
        $this->search = $value;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.homepage.tables.tags-table', [
            'tags' => $this->tags,
        ]);
    }
}
