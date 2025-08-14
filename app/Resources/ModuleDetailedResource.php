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
        $questions = $this->filterArray([
            'description' => $this->questions_description,
            'estimatedTime' => $this->questions_estimated_time,
            'size' => $this->questions_count,
            'list' => $questionsList,
        ]);
        if ($questionsList) {
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


