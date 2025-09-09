<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepResource extends JsonResource
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
            'program_id' => $this->program_id,
            'program_name' => $this->whenLoaded('program', $this->program?->name),
            'name' => $this->name,
            'type' => $this->type,
            'type_text' => $this->type_display,
            'time' => $this->time_to_finish,
            'time_type' => $this->time_type_display,
            'duration' => $this->formatted_duration,
        ];

        // Add user progress using ContextStatusService
        if ($request->authUserId) {
            $contextStatusService = app(ContextStatusService::class);
            $array['status'] = $contextStatusService->getStepStatus($request->authUserId, $this->id, $this->program_id);
        }

        return $array;
    }
}
