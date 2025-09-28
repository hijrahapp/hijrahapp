<?php

namespace App\Http\Repositories;

use App\Models\Category;
use App\Models\Enrichment;
use App\Models\Interest;
use App\Models\Tag;
use App\Models\UserEnrichment;
use App\Resources\CategoryResource;
use App\Resources\EnrichmentResource;
use Illuminate\Database\Eloquent\Collection;

class EnrichmentRepository
{
    /**
     * Get all enrichments with optional filters.
     */
    public function getAllWithFilters(?array $categories = null, ?string $type = null, ?string $search = null): Collection
    {
        $query = Enrichment::query();

        // Categories filter
        if ($categories && ! empty($categories)) {
            $query->where(function ($q) use ($categories) {
                foreach ($categories as $categoryId) {
                    $q->orWhereJsonContains('categories', (int) $categoryId);
                }
            });
        }

        // Type filter
        if ($type) {
            $query->where('type', $type);
        }

        // Text search in title, type, category, interests, and tags
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search in title
                $q->where('title', 'like', "%{$search}%");

                // Search in type (check both English and Arabic lookups)
                $types = ['article', 'video', 'audio', 'book', 'short-video'];
                foreach ($types as $type) {
                    $englishLabel = __('lookups.'.$type, [], 'en');
                    $arabicLabel = __('lookups.'.$type, [], 'ar');
                    if (stripos($englishLabel, $search) !== false || stripos($arabicLabel, $search) !== false) {
                        $q->orWhere('type', $type);
                    }
                }

                // Search in interests
                $interestIds = Interest::where('name', 'like', "%{$search}%")->where('active', true)->pluck('id');
                if ($interestIds->isNotEmpty()) {
                    foreach ($interestIds as $interestId) {
                        $q->orWhereJsonContains('interests', $interestId);
                    }
                }

                // Search in tags
                $tagIds = Tag::where('title', 'like', "%{$search}%")->pluck('id');
                if ($tagIds->isNotEmpty()) {
                    foreach ($tagIds as $tagId) {
                        $q->orWhereJsonContains('tags', $tagId);
                    }
                }

                // Search in categories
                $categoryIds = Category::where('name', 'like', "%{$search}%")->pluck('id');
                if ($categoryIds->isNotEmpty()) {
                    foreach ($categoryIds as $categoryId) {
                        $q->orWhereJsonContains('categories', $categoryId);
                    }
                }
            });
        }

        return $query->where('active', true)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get explore enrichments data.
     */
    public function getExploreData(): array
    {
        $response = [];

        // Get all active categories and add enrichments for each
        $categories = Category::where('active', true)->get();

        $response['categories'] = CategoryResource::collection($categories);
        $response['new'] = EnrichmentResource::collection(Enrichment::where('active', true)->orderBy('created_at', 'desc')->limit(10)->get());
        $response['short-videos'] = EnrichmentResource::collection(Enrichment::where('type', 'short-video')->where('active', true)->orderBy('created_at', 'desc')->limit(10)->get());

        foreach ($categories as $category) {
            $response['categoriesContents'][] = [
                'name' => $category->name,
                'list' => EnrichmentResource::collection(Enrichment::whereJsonContains('categories', $category->id)
                    ->where('active', true)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()),
            ];
        }

        return $response;
    }

    /**
     * Find enrichment by ID.
     */
    public function findById(int $id): ?Enrichment
    {
        return Enrichment::find($id);
    }

    /**
     * Toggle like status for a user and enrichment.
     */
    public function toggleLike(int $userId, int $enrichmentId): array
    {
        $userEnrichment = UserEnrichment::firstOrCreate(
            ['user_id' => $userId, 'enrichment_id' => $enrichmentId],
            ['like' => false, 'favorite' => false]
        );

        $userEnrichment->like = ! $userEnrichment->like;
        $userEnrichment->save();

        return [
            'is_liked' => $userEnrichment->like,
            'was_created' => $userEnrichment->wasRecentlyCreated,
        ];
    }

    /**
     * Toggle favorite status for a user and enrichment.
     */
    public function toggleFavorite(int $userId, int $enrichmentId): array
    {
        $userEnrichment = UserEnrichment::firstOrCreate(
            ['user_id' => $userId, 'enrichment_id' => $enrichmentId],
            ['like' => false, 'favorite' => false]
        );

        $userEnrichment->favorite = ! $userEnrichment->favorite;
        $userEnrichment->save();

        return [
            'is_favorited' => $userEnrichment->favorite,
            'was_created' => $userEnrichment->wasRecentlyCreated,
        ];
    }

    /**
     * Get user interaction status for an enrichment.
     */
    public function getUserInteraction(int $userId, int $enrichmentId): ?UserEnrichment
    {
        return UserEnrichment::where('user_id', $userId)
            ->where('enrichment_id', $enrichmentId)
            ->first();
    }
}
