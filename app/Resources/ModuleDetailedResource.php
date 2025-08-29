<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Services\ContextStatusService;
use App\Services\ResultCalculationOptimizedService;
use App\Services\ResultCalculationService;
use App\Traits\HasTagTitles;
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

        $methodologyId = request()->route('methodologyId');
        $pillarId = $this->pillar_id ?? request()->route('pillarId');
        $sequence = null;

        // Prefer pivot meta when methodologyId (and optionally pillarId) are provided
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
                    // No sequence column in pillar_module; derive order by creation id within this pillar + methodology
                    $orderedModuleIds = \DB::table('pillar_module')
                        ->where('methodology_id', (int) $methodologyId)
                        ->where('pillar_id', (int) $pillarId)
                        ->orderBy('id')
                        ->pluck('module_id')
                        ->toArray();
                    $position = array_search($this->id, $orderedModuleIds, true);
                    if ($position !== false) {
                        $sequence = $position + 1;
                    }
                }
            } else {
                $mm = \DB::table('methodology_module')
                    ->where('methodology_id', (int) $methodologyId)
                    ->where('module_id', $this->id)
                    ->first();
                if ($mm) {
                    $pivotDescription = property_exists($mm, 'questions_description') ? $mm->questions_description : null;
                    $pivotEstimatedTime = property_exists($mm, 'questions_estimated_time') ? $mm->questions_estimated_time : null;
                    // No sequence column in methodology_module; derive order by creation id within this methodology
                    $orderedModuleIds = \DB::table('methodology_module')
                        ->where('methodology_id', (int) $methodologyId)
                        ->orderBy('id')
                        ->pluck('module_id')
                        ->toArray();
                    $position = array_search($this->id, $orderedModuleIds, true);
                    if ($position !== false) {
                        $sequence = $position + 1;
                    }
                }
            }
        }
        if ($sequence !== null) {
            $payload['details'] = $this->filterArray(array_merge($payload['details'], ['sequence' => $sequence]));
        }

        $questionsRepo = new QuestionRepository;
        $contextStatusService = new ContextStatusService;
        $questions = [];
        if ($pillarId && $methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('module', $this->id, $methodologyId, $pillarId);
        } elseif ($methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('module', $this->id, $methodologyId);
        }
        $questions['description'] = $pivotDescription;
        $questions['estimatedTime'] = $pivotEstimatedTime;
        $questions['size'] = count($questions['list']);
        $questions['status'] = $this->user_id ? $contextStatusService->getModuleStatus($this->user_id, $this->id, $methodologyId, $pillarId) : 'not_started';
        $questions = $this->filterArray($questions);
        unset($questions['list']);
        $payload['questions'] = $questions;

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
        $pillarId = $this->pillar_id ?? request()->route('pillarId');
        if ($this->user_id && $methodologyId) {
            return $service->calculateModuleResult($this->user_id, $this->id, (int) $methodologyId, $pillarId ? (int) $pillarId : null);
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
}
