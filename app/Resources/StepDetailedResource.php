<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepDetailedResource extends JsonResource
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
            'header' => $this->header,
            'content' => $this->content,
            'description' => $this->description,
            'content_url' => $this->content_url,
            'content_image' => $this->content_image,
            'advices' => $this->advices,
            'challenges' => $this->challenges,
        ];

        // Add user progress using ContextStatusService
        if ($request->authUserId) {
            $contextStatusService = app(ContextStatusService::class);
            $array['status'] = $contextStatusService->getStepStatus($request->authUserId, $this->id, $this->program_id);
            $progress = $this->progressForUser($request->authUserId);

            if ($progress) {
                $array['thought'] = $progress->thought;
                $array['score'] = $progress->score;
                $array['challenges_done'] = $progress->challenges_done;
                $array['percentage'] = $progress->percentage ?? 0;
                $array['started_at'] = $progress->started_at;
                $array['completed_at'] = $progress->completed_at;
            }
        }

        return $array;
    }
}
