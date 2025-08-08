<?php

namespace App\Livewire\Homepage\Tables;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
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
            $note = null;
        } else {
            $title = __('messages.deactivate_tag_title');
            $message = __('messages.deactivate_tag_message');
            $action = __('messages.deactivate_action');
            $note = __('messages.deactivate_tag_note');
        }
        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeTagStatus',
            'object' => $request
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function openTagDeleteModal($request) {
        $modal = [
            "title" => __('messages.delete_tag_title'),
            "message" => __("messages.delete_tag_message"),
            "note" => __("messages.delete_tag_note"),
            "action" => __("messages.delete_action"),
            "callback" => "deleteTag",
            "object" => $request
        ];
        $this->dispatch('openConfirmationModal', $modal);
    }

    public function changeTagStatus($request)
    {
        $tag = Tag::findOrFail($request['id']);
        $tag->active = $request['active'];
        $tag->save();
        if ($tag->active === false) {
            // On deactivation, remove the tag from all related entities
            $this->removeTagFromAllEntities((int) $tag->id);
        }
        $this->dispatch('refreshTable');
    }

    public function deleteTag($request)
    {
        $tag = Tag::findOrFail($request['id']);
        // Remove tag from all related entities' tags arrays
        $this->removeTagFromAllEntities((int) $tag->id);
        // Delete the tag itself
        $tag->delete();
        $this->dispatch('refreshTable');
    }

    private function removeTagFromAllEntities(int $tagId): void
    {
        // Questions
        Question::whereJsonContains('tags', $tagId)->chunkById(200, function ($questions) use ($tagId) {
            foreach ($questions as $question) {
                $tags = array_values(array_filter((array) $question->tags, fn ($id) => (int) $id !== $tagId));
                $question->tags = $tags;
                $question->save();
            }
        });

        // Pillars
        Pillar::whereJsonContains('tags', $tagId)->chunkById(200, function ($pillars) use ($tagId) {
            foreach ($pillars as $pillar) {
                $tags = array_values(array_filter((array) $pillar->tags, fn ($id) => (int) $id !== $tagId));
                $pillar->tags = $tags;
                $pillar->save();
            }
        });

        // Modules
        Module::whereJsonContains('tags', $tagId)->chunkById(200, function ($modules) use ($tagId) {
            foreach ($modules as $module) {
                $tags = array_values(array_filter((array) $module->tags, fn ($id) => (int) $id !== $tagId));
                $module->tags = $tags;
                $module->save();
            }
        });

        // Methodologies
        Methodology::whereJsonContains('tags', $tagId)->chunkById(200, function ($methodologies) use ($tagId) {
            foreach ($methodologies as $methodology) {
                $tags = array_values(array_filter((array) $methodology->tags, fn ($id) => (int) $id !== $tagId));
                $methodology->tags = $tags;
                $methodology->save();
            }
        });
    }

    public function render()
    {
        return view('livewire.homepage.tables.tags-table', [
            'tags' => $this->tags,
        ]);
    }
}
