<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'definition' => $this->definition,
            'objectives' => $this->objectives,
            'steps_count' => $this->whenLoaded('stepsList', function () {
                return $this->stepsList->count();
            }),
            'completed_steps_count' => 0,
        ];

        // Add program status for the authenticated user
        if ($request->authUserId) {
            $contextStatusService = app(ContextStatusService::class);
            $array['status'] = $contextStatusService->getProgramStatus($request->authUserId, $this->id);
            $array['completed_steps_count'] = $contextStatusService->getCompletedStepsCount($request->authUserId, $this->id);
        }

        // Include scoring information if this program comes from user eligibility query
        if (isset($this->user_score)) {
            $array['module_id'] = $this->qualifying_module_id;
            $array['module_name'] = $this->module_name;
            $array['methodology_id'] = $this->methodology_id;
            $array['methodology_name'] = $this->methodology_name;

            // Include pillar information only if available
            if ($this->pillar_id) {
                $array['pillar_id'] = $this->pillar_id;
                $array['pillar_name'] = $this->pillar_name;
            }

            // $array['eligibility'] = [
            //     'user_score' => round($this->user_score, 2),
            //     'score_range' => [
            //         'min_score' => (float) $this->min_score,
            //         'max_score' => (float) $this->max_score,
            //     ],
            // ];
        }

        return $array;
    }
}
