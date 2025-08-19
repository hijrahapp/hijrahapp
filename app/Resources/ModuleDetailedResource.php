<?php

namespace App\Resources;

use App\Traits\HasTagTitles;
use App\Services\ResultCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleDetailedResource extends JsonResource
{
    use HasTagTitles;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'id' => $this->id,
        ];

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
        $payload['details'] = $this->filterArray($details);

        $questionsList = $this->relationLoaded('questions') && $this->questions && $this->questions->isNotEmpty()
            ? QuestionResource::collection($this->questions)
            : null;

        // Prefer pivot meta when methodologyId (and optionally pillarId) are provided
        $methodologyId = request()->route('methodologyId');
        $pillarId = $this->pillar_id ?? request()->route('pillarId');
        $pivotDescription = null;
        $pivotEstimatedTime = null;
        if ($methodologyId) {
            if ($pillarId) {
                $pm = \DB::table('pillar_module')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('pillar_id', (int) $pillarId)
                    ->where('module_id', $this->id)
                    ->first();
                if ($pm) {
                    $pivotDescription = property_exists($pm, 'questions_description') ? $pm->questions_description : null;
                    $pivotEstimatedTime = property_exists($pm, 'questions_estimated_time') ? $pm->questions_estimated_time : null;
                }
            } else {
                $mm = \DB::table('methodology_module')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('module_id', $this->id)
                    ->first();
                if ($mm) {
                    $pivotDescription = property_exists($mm, 'questions_description') ? $mm->questions_description : null;
                    $pivotEstimatedTime = property_exists($mm, 'questions_estimated_time') ? $mm->questions_estimated_time : null;
                }
            }
        }

        $questions = $this->filterArray([
            // Todo: add 'type', if any answer of a question leads to any other question then value is dynamic, else simple
            'description' => $pivotDescription !== null ? $pivotDescription : ($this->questions_description ?? null),
            'estimatedTime' => $pivotEstimatedTime !== null ? $pivotEstimatedTime : ($this->questions_estimated_time ?? null),
            'size' => count($questionsList),
            'list' => $questionsList,
        ]);
        if ($questionsList && count($questionsList) > 0) {
            $payload['questions'] = $questions;
        }

        $result = $this->calculateResult();
        if ($result) {
            $payload['result'] = $result;
        }

        return $payload;
    }

    private function calculateResult()
    {
        $service = new ResultCalculationService();
        $methodologyId = request()->route('methodologyId');
        $pillarId = $this->pillar_id ?? request()->route('pillarId');
        if ($this->user_id && $methodologyId) {
            return $service->calculateModuleResult($this->user_id, $this->id, (int) $methodologyId, $pillarId ? (int) $pillarId : null);
        }
        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterArray(array $data): array
    {
        return array_filter($data, function ($value) {
            if ($value === null) return false;
            if (is_string($value) && trim($value) === '') return false;
            if (is_array($value) && count($value) === 0) return false;
            return true;
        });
    }
}


