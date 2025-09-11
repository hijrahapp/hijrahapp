<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramDetailedResource extends JsonResource
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
        ];

        $array['details'] = [
            'name' => $this->name,
            'description' => $this->description,
            'definition' => $this->definition,
            'objectives' => $this->objectives,
            'img_url' => $this->img_url,
            'steps_count' => $this->stepsList->groupBy('type')->map->count(),
        ];

        $array['steps'] = [
            'list' => StepResource::collection($this->stepsList),
            'count' => $this->stepsList->count(),
        ];

        if ($request->authUserId) {
            $contextStatusService = app(ContextStatusService::class);
            $array['details']['status'] = $contextStatusService->getProgramStatus($request->authUserId, $this->id);
        }

        return $array;
    }
}
