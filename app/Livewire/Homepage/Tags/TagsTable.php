<?php

namespace App\Livewire\Homepage\Tags;

use App\Models\Methodology;
use App\Models\Module;
use App\Models\Pillar;
use App\Models\Question;
use App\Models\Tag;
use App\Traits\HasDataTable;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class TagsTable extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithTableReload, HasDataTable;

    public $search = '';
    public $perPage = 10;

    protected string $modelClass = Tag::class;
    protected string $entityName = 'tag';
    protected array $searchFields = ['title'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'changeTagStatus' => 'changeTagStatus',
        'deleteTag' => 'deleteTag',
        'changeStatus' => 'changeTagStatus',
        'deleteRecord' => 'deleteTag',
    ];

    #[Computed]
    public function tags()
    {
        $query = $this->getBaseQuery();
        $page = $this->getPage();
        return $query->paginate($this->perPage, ['*'], 'page', $page);
    }

    public function openTagStatusModal($request) {
        $this->openStatusModal($request);
    }

    public function openTagDeleteModal($request) {
        $this->openDeleteModal($request);
    }

    public function changeTagStatus($request)
    {
        $tag = Tag::findOrFail($request['id']);
        if ($request['active'] === false) {
            // On deactivation, remove the tag from all related entities
            $this->removeTagFromAllEntities((int) $tag->id);
        }
        $this->changeStatus($request);
    }

    public function deleteTag($request)
    {
        $tagId = is_array($request) ? $request['id'] : $request;
        $tag = Tag::findOrFail($tagId);
        // Remove tag from all related entities' tags arrays
        $this->removeTagFromAllEntities((int) $tag->id);
        $this->deleteRecord($tagId);
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
        return view('livewire.homepage.tags.tags-table', [
            'tags' => $this->tags,
        ]);
    }
}
