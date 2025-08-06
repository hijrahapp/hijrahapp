<?php

namespace App\Resources;

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
            'type' => $this->type,
            'first_section_name' => $this->first_section_name,
            'second_section_name' => $this->second_section_name,
            'pillars_definition' => $this->pillars_definition,
            'modules_definition' => $this->modules_definition,
            'tags' => $this->getTagTitles($this->tags),
            'pillars' => $this->whenLoaded('pillars', function () {
                return $this->pillars->map(function ($pillar) {
                    return [
                        'id' => $pillar->id,
                        'name' => $pillar->name,
                        'description' => $pillar->description,
                        'definition' => $pillar->definition,
                        'objectives' => $pillar->objectives,
                        'tags' => $this->getTagTitles($pillar->tags),
                        'section' => $pillar->pivot->section ?? null,
                    ];
                });
            }),
            'modules' => $this->whenLoaded('modules', function () {
                return $this->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                        'description' => $module->description,
                        'definition' => $module->definition,
                        'objectives' => $module->objectives,
                        'tags' => $this->getTagTitles($module->tags),
                    ];
                });
            }),
            'questions' => $this->whenLoaded('questions', function () {
                return $this->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'title' => $question->title,
                        'type' => $question->type,
                        'tags' => $this->getTagTitles($question->tags),
                    ];
                });
            }),
            'result' => [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
