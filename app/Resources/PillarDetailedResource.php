<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Traits\HasTagTitles;
use App\Services\ResultCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PillarDetailedResource extends JsonResource
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

        // Questions meta + list (prefer methodology-specific pivot data when methodologyId is provided)
        $questionsList = $this->getGroupedModuleQuestions();
        $methodologyId = request()->route('methodologyId');
        $pivotDescription = null;
        $pivotEstimatedTime = null;
        if ($methodologyId) {
            $pivot = \DB::table('methodology_pillar')
                ->where('methodology_id', (int) $methodologyId)
                ->where('pillar_id', $this->id)
                ->first();
            if ($pivot) {
                $pivotDescription = property_exists($pivot, 'questions_description') ? $pivot->questions_description : null;
                $pivotEstimatedTime = property_exists($pivot, 'questions_estimated_time') ? $pivot->questions_estimated_time : null;
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

        // Modules under this pillar (if loaded)
        $modulesList = $this->relationLoaded('modules') && $this->modules && $this->modules->isNotEmpty()
            ? ModuleResource::collection($this->modules->map(function ($module) {
                $module->setAttribute('user_id', $this->user_id ?? null);
                $module->setAttribute('pillar_id', $this->id);
                return $module;
            }))
            : null;
        if ($modulesList) {
            $payload['modules'] = [
                'list' => $modulesList,
            ];
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
        if ($this->user_id && $methodologyId) {
            return $service->calculatePillarResult($this->user_id, $this->id, (int) $methodologyId);
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

    /**
     * Return module-grouped questions for this pillar within the current methodology
     */
    private function getGroupedModuleQuestions(): array
    {
        $methodologyId = request()->route('methodologyId');
        if (!$methodologyId) {
            return [];
        }

        $modules = $this->modulesForMethodology((int) $methodologyId)->get();
        if ($modules->isEmpty()) {
            return [];
        }

        $questionRepo = new QuestionRepository();
        return $questionRepo->getPillarModuleQuestionsGrouped($methodologyId, $this->id);
    }
}


