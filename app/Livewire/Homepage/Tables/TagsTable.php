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
            $title = __('messages.activate_tag_title');
            $message = __('messages.activate_tag_message');
            $action = __('messages.activate_action');
        } else {
            $title = __('messages.deactivate_tag_title');
            $message = __('messages.deactivate_tag_message');
            $action = __('messages.deactivate_action');
        }
        $callback = 'changeTagStatus';
        $this->dispatch('openConfirmationModal', $title, $message, $action, $callback, $request);
    }

    public function openTagDeleteModal($request) {
        $title = __('messages.delete_tag_title');
        $message = __('messages.delete_tag_message');
        $action = __('messages.delete_action');
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
