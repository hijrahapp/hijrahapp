<?php

namespace App\Traits;

use App\Models\Tag;

trait HasTagTitles
{
    /**
     * Convert tag IDs to tag titles
     *
     * @param array|null $tagIds
     * @return array
     */
    public function getTagTitles(?array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }

        return Tag::whereIn('id', $tagIds)
            ->pluck('title')
            ->toArray();
    }
} 