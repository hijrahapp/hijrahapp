<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObjectiveDetailedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'program' => $this->whenLoaded('program', new ProgramResource($this->program)),
            'name' => $this->name,
            'type' => $this->type,
            'type_display' => $this->type_display,
            'time_to_finish' => $this->time_to_finish,
            'time_type' => $this->time_type,
            'time_type_display' => $this->time_type_display,
            'formatted_duration' => $this->formatted_duration,
            'type_specific_data' => $this->getTypeData(),
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
