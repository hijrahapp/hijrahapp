<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Traits\HasTagTitles;
use App\Services\ResultCalculationService;
use App\Services\ResultCalculationOptimizedService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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

        $questionsRepo = new QuestionRepository();
        $questions = [];
        if ($pillarId && $methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('module', $this->id, $methodologyId, $pillarId);
        } elseif ($methodologyId) {
            $questions = $questionsRepo->getQuestionsByContext('module', $this->id, $methodologyId);
        }
        $questions['description'] = $pivotDescription;
        $questions['estimatedTime'] = $pivotEstimatedTime;
        $questions['size'] = count($questions['list']);
        $questions = $this->filterArray($questions);
        unset($questions["list"]);
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
            ? new ResultCalculationOptimizedService()
            : new ResultCalculationService();
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


