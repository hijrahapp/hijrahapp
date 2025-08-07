<?php

namespace App\Resources;

use App\Services\ResultCalculationService;
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
            'tags' => $this->getTagTitles($this->tags),
            'section' => $this->pivot->section ?? null,
            'modules' => ModuleResource::collection($this->modules->map(function ($module) {
                $module->setAttribute('user_id', $this->user_id ?? null);
                return $module;
            })),
            'questions' => QuestionResource::collection($this->questions),
            'result' => $this->calculateResult(),
        ];
    }

    /**
     * Calculate result for this pillar
     */
    private function calculateResult(): array
    {
        $service = new ResultCalculationService();
        
        if($this->user_id && request()->route('methodologyId')){
            return $service->calculatePillarResult($this->user_id, $this->id, request()->route('methodologyId'));
        } else {
            return [];
        }
    }
}