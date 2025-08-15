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

        // Pillar-level questions meta + list (if loaded)
        $questionsList = $this->getGroupedModuleQuestions();
        $questions = $this->filterArray([
            'description' => $this->questions_description,
            'estimatedTime' => $this->questions_estimated_time,
            'size' => $this->questions_count,
            'list' => $questionsList,
        ]);
        if ($questionsList) {
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

        return $modules->map(function ($module) use ($methodologyId, $questionRepo) {
            $questions = $questionRepo->getQuestionsByContext('module', $module->id, (int) $methodologyId, $this->id);
            return [
                'module' => [
                    'id' => $module->id,
                    'name' => $module->name,
                    'description' => $module->description,
                ],
                'questions' => QuestionResource::collection($questions),
            ];
        })->values()->all();
    }
}


