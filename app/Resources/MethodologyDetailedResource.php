<?php

namespace App\Resources;

use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MethodologyDetailedResource extends JsonResource
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
            'type' => $this->type,
            'details' => [
                'name' => $this->name,
                'description' => $this->description,
                'definition' => $this->definition,
                'objectives' => $this->objectives,
                'tags' => $this->getTagTitles($this->tags),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'sections' => [
                [
                    'id' => 1,
                    'name' => $this->first_section_name,
                    'description' => $this->first_section_description,
                ],
                [
                    'id' => 2,
                    'name' => $this->second_section_name,
                    'description' => $this->second_section_description,
                ]
            ],
            'questions' => [
                'description' => $this->questions_description,
                'estimatedTime' => $this->questions_estimated_time,
                'size' => $this->questions_count,
                'list' => $this->whenLoaded('questions', function () {
                    return QuestionResource::collection($this->questions);
                })
            ],
            'pillars' => [
                'definition' => $this->pillars_definition,
                'list' => $this->whenLoaded('pillars', function () {
                    return PillarResource::collection($this->pillars);
                }),
            ],
            'modules' => [
                'definition' => $this->modules_definition,
                'list' => $this->whenLoaded('modules', function () {
                    return ModuleResource::collection($this->modules);
                }),
            ],
        ];
    }
} 