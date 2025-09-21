<?php

namespace App\Resources;

use App\Http\Repositories\EnrichmentRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrichmentDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'categoryLabel' => __('lookups.'.$this->category),
            'type' => $this->type,
            'typeLabel' => __('lookups.'.$this->type),
            'img_url' => $this->img_url,
            'header' => $this->header,
            'content' => $this->content,
            'content_url' => $this->content_url,
            'content_image' => $this->content_image,
            // 'interests' => InterestResource::collection($this->getInterestsObjects()),
            'tags' => $this->getTagsObjects()->map(function ($tag) {
                return $tag->title;
            }),
            'createdAt' => $this->formatLocalizedDate($this->created_at),
            'isLiked' => $this->getUserInteraction('like', $request),
            'isFavorited' => $this->getUserInteraction('favorite', $request),
        ];
    }

    /**
     * Format date based on app locale (Arabic or English).
     */
    private function formatLocalizedDate($date): ?string
    {
        if (! $date) {
            return null;
        }

        $locale = app()->getLocale();

        if ($locale === 'ar') {
            $arabicMonths = [
                1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
            ];

            $day = $date->day;
            $month = $arabicMonths[$date->month];
            $year = $date->year;

            return "{$year} {$month} {$day}";
        } else {
            // English format: "27 August 2025"
            return $date->format('j F Y');
        }
    }

    /**
     * Get user interaction status (like or favorite).
     */
    private function getUserInteraction(string $type, Request $request): bool
    {
        $userId = $request->authUserId;

        if (! $userId) {
            return false;
        }

        $repository = app(EnrichmentRepository::class);
        $userEnrichment = $repository->getUserInteraction($userId, $this->id);

        return $userEnrichment ? $userEnrichment->{$type} : false;
    }
}
