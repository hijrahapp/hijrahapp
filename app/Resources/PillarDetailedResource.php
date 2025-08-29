<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Services\ContextStatusService;
use App\Services\ResultCalculationOptimizedService;
use App\Services\ResultCalculationService;
use App\Traits\HasTagTitles;
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

        $methodologyId = request()->route('methodologyId');
        $sequence = null;
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
                $orderedPillarIds = \DB::table('methodology_pillar')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('section', $pivot->section)
                    ->orderBy('id')
                    ->pluck('pillar_id')
                    ->toArray();
                $position = array_search($this->id, $orderedPillarIds, true);
                if ($position !== false) {
                    $sequence = $position + 1;
                }
            }
        }
        if ($sequence !== null) {
            $payload['details'] = $this->filterArray(array_merge($payload['details'], ['sequence' => $sequence]));
        }

        $questionsRepo = new QuestionRepository;
        $contextStatusService = new ContextStatusService;
        $questions = ['list' => []];
        if ($methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('pillar', $this->id, $methodologyId);
        }

        // Ensure we have a valid questions structure
        if (! isset($questions['list'])) {
            $questions['list'] = [];
        }

        $questions['description'] = $pivotDescription;
        $questions['estimatedTime'] = $pivotEstimatedTime;
        $questions['size'] = count($questions['list']);
        $questions['status'] = $this->user_id ? $contextStatusService->getPillarStatus($this->user_id, $this->id, $methodologyId) : 'not_started';
        unset($questions['list']);
        $questions = $this->filterArray($questions);
        $payload['questions'] = $questions;

        // Modules under this pillar (fetch for specific methodology if available, else all modules)
        $modulesList = null;
        if ($methodologyId) {
            // Fetch modules for the specific methodology
            $methodologyModules = $this->modulesForMethodology((int) $methodologyId)->get();
            if ($methodologyModules->isNotEmpty()) {
                $modulesList = ModuleResource::collection($methodologyModules->map(function ($module) {
                    $module->setAttribute('user_id', $this->user_id ?? null);
                    $module->setAttribute('pillar_id', $this->id);

                    return $module;
                }));
            }
        } else {
            // Fallback to all modules under this pillar (if loaded)
            if ($this->relationLoaded('modules') && $this->modules && $this->modules->isNotEmpty()) {
                $modulesList = ModuleResource::collection($this->modules->map(function ($module) {
                    $module->setAttribute('user_id', $this->user_id ?? null);
                    $module->setAttribute('pillar_id', $this->id);

                    return $module;
                }));
            }
        }
        if ($modulesList) {
            $payload['modules'] = [
                'list' => $modulesList,
            ];
        }

        if (config('app.features.result_calculation')) {
            $result = $this->calculateResult();
            if ($result) {
                $payload['result'] = $result;
            }
        }

        return $payload;
    }

    private function calculateResult()
    {
        $service = config('app.features.optimized_calculation')
            ? new ResultCalculationOptimizedService
            : new ResultCalculationService;
        $methodologyId = request()->route('methodologyId');
        if ($this->user_id && $methodologyId) {
            return $service->calculatePillarResult($this->user_id, $this->id, (int) $methodologyId);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
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

    /**
     * Return module-grouped questions for this pillar within the current methodology
     */
    private function getGroupedModuleQuestions(): array
    {
        $methodologyId = request()->route('methodologyId');
        if (! $methodologyId) {
            return [];
        }

        $modules = $this->modulesForMethodology((int) $methodologyId)->get();
        if ($modules->isEmpty()) {
            return [];
        }

        $questionRepo = new QuestionRepository;

        return $questionRepo->getPillarModuleQuestionsGrouped($methodologyId, $this->id);
    }
}
