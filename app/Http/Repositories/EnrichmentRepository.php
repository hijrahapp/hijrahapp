<?php

namespace App\Http\Repositories;

use App\Models\Enrichment;
use App\Models\Interest;
use App\Models\Tag;
use App\Models\UserEnrichment;
use Illuminate\Database\Eloquent\Collection;

class EnrichmentRepository
{
    /**
     * Get all enrichments with optional filters.
     */
    public function getAllWithFilters(?string $category = null, ?string $type = null, ?string $search = null): Collection
    {
        $query = Enrichment::query();

        // Category filter
        if ($category) {
            $query->where('category', $category);
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

                // Search in category (check both English and Arabic lookups)
                $categories = ['health', 'money', 'family', 'spirituality'];
                foreach ($categories as $category) {
                    $englishLabel = __('lookups.'.$category, [], 'en');
                    $arabicLabel = __('lookups.'.$category, [], 'ar');
                    if (stripos($englishLabel, $search) !== false || stripos($arabicLabel, $search) !== false) {
                        $q->orWhere('category', $category);
                    }
                }

                // Search in interests
                $interestIds = Interest::where('name', 'like', "%{$search}%")->pluck('id');
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
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get explore enrichments data.
     */
    public function getExploreData(): array
    {
        return [
            'new' => Enrichment::orderBy('created_at', 'desc')->limit(10)->get(),
            'short-videos' => Enrichment::where('type', 'short-video')->orderBy('created_at', 'desc')->limit(10)->get(),
            'health' => Enrichment::where('category', 'health')->orderBy('created_at', 'desc')->limit(10)->get(),
            'money' => Enrichment::where('category', 'money')->orderBy('created_at', 'desc')->limit(10)->get(),
            'family' => Enrichment::where('category', 'family')->orderBy('created_at', 'desc')->limit(10)->get(),
            'spirituality' => Enrichment::where('category', 'spirituality')->orderBy('created_at', 'desc')->limit(10)->get(),
        ];
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
