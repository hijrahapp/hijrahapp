<?php

namespace App\Resources;

use App\Services\ResultCalculationService;
use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MethodologyResource extends JsonResource
{
    use HasTagTitles;

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
            'objectives' => $this->objectives,
            'imgUrl' => $this->img_url,
            'type' => $this->type,
            'first_section_name' => $this->first_section_name,
            'second_section_name' => $this->second_section_name,
            'pillars_definition' => $this->pillars_definition,
            'modules_definition' => $this->modules_definition,
            'tags' => $this->getTagTitles($this->tags),
            'pillars' => $this->whenLoaded('pillars', function () {
                return PillarResource::collection($this->pillars);
            }),
            'modules' => $this->whenLoaded('modules', function () {
                return ModuleResource::collection($this->modules->map(function ($module) {
                    $module->setAttribute('user_id', $this->user_id ?? null);
                    // No pillar context when listing modules at methodology level
                    return $module;
                }));
            }),
            'questions' => $this->whenLoaded('questions', function () {
                return QuestionResource::collection($this->questions);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
