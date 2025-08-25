<?php

namespace App\Resources;

use App\Http\Repositories\QuestionRepository;
use App\Services\ResultCalculationService;
use App\Services\ResultCalculationOptimizedService;
use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PillarResource extends JsonResource
{
    use HasTagTitles;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'definition' => $this->definition,
            'objectives' => $this->objectives,
            'imgUrl' => $this->img_url,
            'tags' => $this->getTagTitles($this->tags),
            'section' => $this->pivot->section ?? null,
            'status' => $this->calculateStatus(),
            'result' => $this->calculateResult(),
        ];
    }

    /**
     * Calculate result for this pillar
     */
    private function calculateResult()
    {
        $service = config('app.features.optimized_calculation')
            ? new ResultCalculationOptimizedService()
            : new ResultCalculationService();

        if($this->user_id && request()->route('methodologyId')){
            return $service->calculatePillarResult($this->user_id, $this->id, request()->route('methodologyId'));
        } else {
            return null;
        }
    }

    /**
     * Calculate completion status for this pillar based on user answers
     * not_started | in_progress | completed
     */
    private function calculateStatus(): ?string
    {
        $methodologyId = request()->route('methodologyId');

        if (!$this->user_id || !$methodologyId) {
            return null;
        }

        // Use ContextStatusService directly for status
        $statusService = new \App\Services\ContextStatusService();
        $status = $statusService->getPillarStatus($this->user_id, $this->id, (int) $methodologyId);
        return $status ?? null;
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
