<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrichmentResource extends JsonResource
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
            'imgUrl' => $this->img_url,
        ];
    }
}
