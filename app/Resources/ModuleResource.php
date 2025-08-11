<?php

namespace App\Resources;

use App\Services\ResultCalculationService;
use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
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
            'tags' => $this->getTagTitles($this->tags),
            'questions' => QuestionResource::collection($this->questions),
            'status' => $this->calculateStatus(),
            'result' => $this->calculateResult(),
        ];
    }

    /**
     * Calculate result for this module
     */
    private function calculateResult()
    {
        $service = new ResultCalculationService();

        if($this->user_id && request()->route('methodologyId') && request()->route('pillarId')) {
            return $service->calculateModuleResult($this->user_id, $this->id, request()->route('methodologyId') ,request()->route('pillarId'));
        } else {
            return null;
        }
    }

    /**
     * Calculate completion status for this module based on user answers
     * not_started | in_progress | completed
     */
    private function calculateStatus(): ?string
    {
        $methodologyId = request()->route('methodologyId');
        $pillarId = request()->route('pillarId');

        if (!$this->user_id || !$methodologyId) {
            return null;
        }

        $service = new ResultCalculationService();
        return $service->getModuleStatus($this->user_id, $this->id, (int) $methodologyId, $pillarId ? (int) $pillarId : null);
    }
}
