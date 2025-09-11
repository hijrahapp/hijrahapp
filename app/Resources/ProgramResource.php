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
            'img_url' => $this->img_url,
            'steps_count' => $this->when(
                isset($this->steps_count),
                function () {
                    return $this->steps_count;
                }
            ),
            'completed_steps_count' => 0,
        ];

        // Handle data from repository queries (stdClass objects) vs Eloquent models
        if (isset($this->user_status)) {
            $array['status'] = $this->user_status;
        } elseif (isset($this->status)) {
            $array['status'] = $this->status;
        } elseif ($request->authUserId) {
            // Fallback to service for Eloquent models
            $contextStatusService = app(ContextStatusService::class);
            $array['status'] = $contextStatusService->getProgramStatus($request->authUserId, $this->id);
            $array['completed_steps_count'] = $contextStatusService->getCompletedStepsCount($request->authUserId, $this->id);
        }

        // Include modules if available from repository
        // if (isset($this->modules)) {
        // $array['modules'] = $this->modules;
        // }

        // Include timestamps if available
        if (isset($this->started_at)) {
            $array['started_at'] = $this->started_at;
        }
        if (isset($this->completed_at)) {
            $array['completed_at'] = $this->completed_at;
        }

        // Include scoring information if this program comes from user eligibility query
        if (isset($this->qualifying_module)) {
            $array['module_id'] = $this->qualifying_module['id'];
            $array['module_name'] = $this->qualifying_module['name'];
            if ($this->qualifying_module['pillar']) {
                $array['pillar_id'] = $this->qualifying_module['pillar']['id'];
                $array['pillar_name'] = $this->qualifying_module['pillar']['name'];
            }
            if ($this->qualifying_module['methodology']) {
                $array['methodology_id'] = $this->qualifying_module['methodology']['id'];
                $array['methodology_name'] = $this->qualifying_module['methodology']['name'];
            }
        }

        return $array;
    }
}
