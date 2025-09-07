<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramDetailedResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'definition' => $this->definition,
            'objectives' => $this->whenLoaded('objectives', function () {
                return ObjectiveResource::collection($this->objectives);
            }),
            'objectives_count' => $this->whenLoaded('objectives', function () {
                return $this->objectives->count();
            }),
            'objectives_count_by_type' => $this->whenLoaded('objectives', function () {
                return $this->objectives->groupBy('type')->map->count();
            }),
            'modules' => $this->whenLoaded('modules', function () {
                return $this->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                        'description' => $module->description,
                        'definition' => $module->definition,
                        'objectives' => $module->objectives,
                        'img_url' => $module->img_url,
                        'methodology_id' => $module->pivot->methodology_id,
                        'pillar_id' => $module->pivot->pillar_id,
                        'min_score' => (float) $module->pivot->min_score,
                        'max_score' => (float) $module->pivot->max_score,
                        'linked_at' => $module->pivot->created_at,
                        'updated_at' => $module->pivot->updated_at,
                    ];
                });
            }),
            'modules_count' => $this->whenLoaded('modules', function () {
                return $this->modules->count();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
