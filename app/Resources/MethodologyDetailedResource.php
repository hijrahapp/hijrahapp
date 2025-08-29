<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Services\ContextStatusService;
use App\Services\ResultCalculationService;
use App\Services\ResultCalculationOptimizedService;
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
                $sectionResults = null;
                if (config('app.features.result_calculation')) {
                    $service = config('app.features.optimized_calculation')
                        ? new ResultCalculationOptimizedService()
                        : new ResultCalculationService();
                    if ($this->user_id) {
                        $sectionResults = [
                            1 => $service->calculateSectionResult($this->user_id, $this->id, 1),
                            2 => $service->calculateSectionResult($this->user_id, $this->id, 2),
                        ];
                    }
                }

                // Gate results visibility: if user hasn't answered any question in BOTH sections,
                // set results of both sections to null; otherwise return both results
                $answered1 = is_array($sectionResults[1] ?? null)
                    ? (int)($sectionResults[1]['summary']['answered_questions'] ?? 0)
                    : 0;
                $answered2 = is_array($sectionResults[2] ?? null)
                    ? (int)($sectionResults[2]['summary']['answered_questions'] ?? 0)
                    : 0;
                $hideBothResults = ($answered1 + $answered2) === 0;

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

                if(!$hideBothResults) {
                    $payload['sections'][0]['result'] = $sectionResults[1];
                    $payload['sections'][1]['result'] = $sectionResults[2];
                }
            }
        }

        // Filter out null/empty values from details (preserve numeric 0)
        $payload['details'] = $this->filterArray($details);

        // Questions block (include list only when loaded and non-empty)
        if ($sectionNumber == null ||$sectionNumber !== 2) {
            $questionsRepo = new QuestionRepository();
            $contextStatusService = new ContextStatusService();
            $questions = $questionsRepo->getQuestionsByContext('methodology', $this->id);
            $questions['description'] = $this->questions_description;
            $questions['estimatedTime'] = $this->questions_estimated_time;
            $questions['size'] = count($questions['list']);
            $questions['status'] = $this->user_id ? $contextStatusService->getMethodologyStatus($this->user_id, $this->id) : 'not_started';
            unset($questions["list"]);
            $questions = $this->filterArray($questions);
            $payload['questions'] = $questions;
        }

        // Pillars block
        $pillarsList = $this->relationLoaded('pillars') && $this->pillars && $this->pillars->isNotEmpty()
            ? PillarResource::collection($this->pillars->map(function ($pillar) {
                $pillar->setAttribute('user_id', $this->user_id ?? null);
                return $pillar;
            }))
            : null;
        $pillars = $this->filterArray([
            'list' => $pillarsList
        ]);
        if($sectionNumber === 1){
            $pillars['definition'] = $this->first_section_pillars_definition;
        } elseif($sectionNumber === 2){
            $pillars['definition'] = $this->second_section_pillars_definition;
        } else {
            $pillars['definition'] = $this->pillars_definition;
        }
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
        if (config('app.features.result_calculation') && ($this->type !== 'twoSection' || $sectionNumber === 1)) {
            $result = $this->calculateResult();
            if ($result) {
                $payload['result'] = $result;
            }
        }

        return $payload;
    }

    /**
     * Calculate result for this methodology
     */
    private function calculateResult()
    {
        $service = config('app.features.dynamic_questions')
            ? new ResultCalculationOptimizedService()
            : new ResultCalculationService();

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
