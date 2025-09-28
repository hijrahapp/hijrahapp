<?php

namespace App\Livewire\Homepage\Enrichments;

use App\Models\Category;
use App\Models\Enrichment;
use App\Models\Interest;
use App\Models\Tag;
use App\Traits\WithoutUrlPagination;
use App\Traits\WithTableReload;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EnrichmentsTable extends Component
{
    use WithoutUrlPagination, WithTableReload;

    public string $search = '';

    public int $perPage = 10;

    public string $typeFilter = '';

    protected $listeners = [
        'refreshTable' => 'reloadTable',
        'deleteEnrichment' => 'deleteEnrichment',
        'changeEnrichmentStatus' => 'changeEnrichmentStatus',
    ];

    #[Computed]
    public function enrichments()
    {
        return $this->handleReloadState(function () {
            $query = Enrichment::where('title', 'like', '%'.$this->search.'%')
                ->when($this->typeFilter, function ($q) {
                    $q->where('type', $this->typeFilter);
                })
                ->orderBy('created_at', 'desc');

            // Use custom pagination without URL caching
            $page = $this->getPage();

            return $query->paginate($this->perPage, ['*'], 'page', $page);
        });
    }

    #[Computed]
    public function enrichmentTypes()
    {
        return [
            'article' => 'Article',
            'video' => 'Video',
            'audio' => 'Audio',
            'book' => 'Book',
            'short-video' => 'Short Video',
        ];
    }

    public function getCategoryTitles($categoryIds, $limit = 2)
    {
        if (empty($categoryIds) || ! is_array($categoryIds)) {
            return [
                'categories' => [],
                'hasMore' => false,
                'totalCount' => 0,
                'remainingCount' => 0,
            ];
        }

        $allCategories = Category::whereIn('id', $categoryIds)
            ->where('active', true)
            ->pluck('name')
            ->toArray();

        $totalCount = count($allCategories);
        $displayedCategories = array_slice($allCategories, 0, $limit);
        $hasMore = $totalCount > $limit;

        return [
            'categories' => $displayedCategories,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'remainingCount' => max(0, $totalCount - $limit),
        ];
    }

    public function getInterestTitles($interestIds, $limit = 2)
    {
        if (empty($interestIds) || ! is_array($interestIds)) {
            return [
                'interests' => [],
                'hasMore' => false,
                'totalCount' => 0,
                'remainingCount' => 0,
            ];
        }

        $allInterests = Interest::whereIn('id', $interestIds)
            ->where('active', true)
            ->pluck('name')
            ->toArray();

        $totalCount = count($allInterests);
        $displayedInterests = array_slice($allInterests, 0, $limit);
        $hasMore = $totalCount > $limit;

        return [
            'interests' => $displayedInterests,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'remainingCount' => max(0, $totalCount - $limit),
        ];
    }

    public function getTagTitles($tagIds, $limit = 2)
    {
        if (empty($tagIds) || ! is_array($tagIds)) {
            return [
                'tags' => [],
                'hasMore' => false,
                'totalCount' => 0,
                'remainingCount' => 0,
            ];
        }

        $allTags = Tag::whereIn('id', $tagIds)
            ->where('active', true)
            ->pluck('title')
            ->toArray();

        $totalCount = count($allTags);
        $displayedTags = array_slice($allTags, 0, $limit);
        $hasMore = $totalCount > $limit;

        return [
            'tags' => $displayedTags,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'remainingCount' => max(0, $totalCount - $limit),
        ];
    }

    public function openEnrichmentStatusModal($request)
    {
        $enrichment = Enrichment::findOrFail($request['id']);

        if ($request['active']) {
            // Validate enrichment for activation
            $validationResult = $this->validateEnrichmentForActivation($enrichment);
            if (! $validationResult['valid']) {
                $this->dispatch('show-toast', type: 'error', message: $validationResult['message']);

                return;
            }

            $title = 'Activate Enrichment';
            $message = 'Are you sure you want to activate this enrichment?';
            $action = 'Activate';
            $note = null;
        } else {
            $title = 'Deactivate Enrichment';
            $message = 'Are you sure you want to deactivate this enrichment?';
            $action = 'Deactivate';
            $note = 'This enrichment will no longer be visible to users.';
        }

        $modal = [
            'title' => $title,
            'message' => $message,
            'note' => $note,
            'action' => $action,
            'callback' => 'changeEnrichmentStatus',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    /**
     * Validate if an enrichment meets all criteria for activation.
     */
    private function validateEnrichmentForActivation(Enrichment $enrichment): array
    {
        $errors = [];

        // 1. Check basic required fields
        if (empty($enrichment->title)) {
            $errors[] = 'Title is required';
        }

        if (empty($enrichment->description)) {
            $errors[] = 'Description is required';
        }

        if (empty($enrichment->img_url)) {
            $errors[] = 'Enrichment image is required';
        }

        if (empty($enrichment->type)) {
            $errors[] = 'Type is required';
        }

        if (empty($enrichment->categories) || ! is_array($enrichment->categories) || count($enrichment->categories) === 0) {
            $errors[] = 'At least one category is required';
        } else {
            // Check if categories exist and are active
            $validCategories = Category::whereIn('id', $enrichment->categories)
                ->where('active', true)
                ->count();
            if ($validCategories === 0) {
                $errors[] = 'At least one valid active category is required';
            }
        }

        if (empty($enrichment->interests) || ! is_array($enrichment->interests) || count($enrichment->interests) === 0) {
            $errors[] = 'At least one interest is required';
        } else {
            // Check if interests exist and are active
            $validInterests = Interest::whereIn('id', $enrichment->interests)
                ->where('active', true)
                ->count();
            if ($validInterests === 0) {
                $errors[] = 'At least one valid active interest is required';
            }
        }

        // 2. Type-specific content validations
        if ($enrichment->type === 'article') {
            if (empty($enrichment->content)) {
                $errors[] = 'Article content is required for article type';
            }
        } elseif (in_array($enrichment->type, ['video', 'audio', 'book', 'short-video'])) {
            if (empty($enrichment->content_url)) {
                $errors[] = 'Content file/URL is required for '.$enrichment->type.' type';
            }
            if (empty($enrichment->content_image)) {
                $errors[] = 'Content cover image is required for '.$enrichment->type.' type';
            }
        }

        if (empty($errors)) {
            return ['valid' => true, 'message' => ''];
        }

        return [
            'valid' => false,
            'message' => 'Cannot activate enrichment. Please complete the missing required fields in the manage page:<br>• '.implode('<br>• ', $errors),
        ];
    }

    public function changeEnrichmentStatus($request)
    {
        $enrichment = Enrichment::findOrFail($request['id']);

        // If activating, validate the enrichment meets all criteria
        if ($request['active']) {
            $validationResult = $this->validateEnrichmentForActivation($enrichment);
            if (! $validationResult['valid']) {
                $this->dispatch('show-toast', type: 'error', message: $validationResult['message']);

                return;
            }
        }

        $enrichment->active = $request['active'];
        $enrichment->save();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: $request['active'] ? 'Enrichment activated successfully!' : 'Enrichment deactivated successfully!');
    }

    public function openDeleteEnrichmentModal($request)
    {
        $enrichment = Enrichment::findOrFail($request['id']);

        $modal = [
            'title' => 'Delete Enrichment',
            'message' => 'Are you sure you want to delete this enrichment?',
            'note' => 'This action cannot be undone.',
            'action' => 'Delete',
            'callback' => 'deleteEnrichment',
            'object' => $request,
        ];

        $this->dispatch('openConfirmationModal', $modal);
    }

    public function deleteEnrichment($request)
    {
        $enrichment = Enrichment::findOrFail($request['id']);
        $enrichment->delete();
        $this->reloadTable();
        $this->dispatch('show-toast', type: 'success', message: 'Enrichment deleted successfully!');
    }

    public function render()
    {
        return view('livewire.homepage.enrichments.enrichments-table', [
            'enrichments' => $this->enrichments,
        ]);
    }
}
