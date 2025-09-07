<?php

namespace App\Resources;

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

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

            $array['eligibility'] = [
                'user_score' => round($this->user_score, 2),
                'score_range' => [
                    'min_score' => (float) $this->min_score,
                    'max_score' => (float) $this->max_score,
                ],
            ];
        }

        return $array;
    }
}
