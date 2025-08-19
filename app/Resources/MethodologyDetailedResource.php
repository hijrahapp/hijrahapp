<?php

namespace App\Resources;

use App\Services\ResultCalculationService;
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
        // If a section number is provided on the model, override details with section-specific fields

        $payload = [
            'id' => $this->id,
            'type' => $this->type,
            // 'details' => $details,
            // sections handled below
            // 'questions' => !empty($questions) ? $questions : null,
            // 'pillars' => !empty($pillars) ? $pillars : null,
            // 'modules' => !empty($modules) ? $modules : null,
            // 'result' => $this->calculateResult(),
        ];

        $sectionNumber = $this->section_number ?? null;

        if ($sectionNumber === 1) {
            $payload['sectionNumber'] = $sectionNumber;

            $details = [
                'name' => $this->first_section_name,
                'description' => $this->first_section_description,
                'definition' => $this->first_section_definition,
                'objectives' => $this->first_section_objectives,
                'imgUrl' => $this->first_section_img_url,
                'tags' => $this->getTagTitles($this->tags),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        } elseif ($sectionNumber === 2) {
            $payload['sectionNumber'] = $sectionNumber;

            $details = [
                'name' => $this->second_section_name,
                'description' => $this->second_section_description,
                'definition' => $this->second_section_definition,
                'objectives' => $this->second_section_objectives,
                'imgUrl' => $this->second_section_img_url,
                'tags' => $this->getTagTitles($this->tags),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        } else {
            $details = [
                'name' => $this->name,
                'description' => $this->description,
                'definition' => $this->definition,
                'objectives' => $this->objectives,
                'imgUrl' => $this->img_url,
                'tags' => $this->getTagTitles($this->tags),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];

            if($this->type === 'twoSection'){
                $payload['sections'] = [
                    [
                        'id' => 1,
                        'name' => $this->first_section_name,
                        'description' => $this->first_section_description,
                        'definition' => $this->first_section_definition,
                        'objectives' => $this->first_section_objectives,
                        'imgUrl' => $this->first_section_img_url,
                    ],
                    [
                        'id' => 2,
                        'name' => $this->second_section_name,
                        'description' => $this->second_section_description,
                        'definition' => $this->second_section_definition,
                        'objectives' => $this->second_section_objectives,
                        'imgUrl' => $this->second_section_img_url,
                    ]
                ];
            }
        }

        // Filter out null/empty values from details (preserve numeric 0)
        $payload['details'] = $this->filterArray($details);

        // Questions block (include list only when loaded and non-empty)
        $questionsList = $this->relationLoaded('questions') && $this->questions && $this->questions->isNotEmpty()
            ? QuestionResource::collection($this->questions)
            : null;
        $questions = $this->filterArray([
            'description' => $this->questions_description,
            'estimatedTime' => $this->questions_estimated_time,
            'size' => $this->questions_count,
            'list' => $questionsList,
        ]);
        if ($questionsList && $questionsList->count() > 0) {
            $payload['questions'] = $this->filterArray($questions);
        }

        // Pillars block
        $pillarsList = $this->relationLoaded('pillars') && $this->pillars && $this->pillars->isNotEmpty()
            ? PillarResource::collection($this->pillars->map(function ($pillar) {
                $pillar->setAttribute('user_id', $this->user_id ?? null);
                return $pillar;
            }))
            : null;
        $pillars = $this->filterArray([
            'definition' => $this->pillars_definition,
            'list' => $pillarsList,
        ]);
        if ($pillarsList) {
            $payload['pillars'] = $this->filterArray($pillars);
        }

        // Modules block
        $modulesList = $this->relationLoaded('modules') && $this->modules && $this->modules->isNotEmpty()
            ? ModuleResource::collection($this->modules->map(function ($module) {
                $module->setAttribute('user_id', $this->user_id ?? null);
                // No pillar context when listing modules at methodology level
                return $module;
            }))
            : null;
        $modules = $this->filterArray([
            'definition' => $this->modules_definition,
            'list' => $modulesList,
        ]);
        if ($modulesList) {
            $payload['modules'] = $this->filterArray($modules);
        }

        // Result block
        $result = $this->calculateResult();
        if ($result) {
            $payload['result'] = $result;
        }

        return $payload;
    }

    /**
     * Calculate result for this methodology
     */
    private function calculateResult()
    {
        $service = new ResultCalculationService();

        if ($this->user_id) {
            return $service->calculateMethodologyResult($this->user_id, $this->id);
        } else {
            return null;
        }
    }

    /**
     * Remove keys whose values are null, empty string, or empty array.
     * Preserve numeric zero values.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterArray(array $data): array
    {
        return array_filter($data, function ($value) {
            if ($value === null) {
                return false;
            }
            if (is_string($value) && trim($value) === '') {
                return false;
            }
            if (is_array($value) && count($value) === 0) {
                return false;
            }
            return true;
        });
    }
}
