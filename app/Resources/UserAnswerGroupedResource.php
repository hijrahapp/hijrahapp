<?php

namespace App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAnswerGroupedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->first()->user_id,
            'context_type' => $this->first()->context_type,
            'context_id' => $this->first()->context_id,
            'question' => new QuestionResource($this->first()->question),
            'userAnswers' => $this->map(function ($userAnswer) {
                return [
                    'id' => $userAnswer->answer->id,
                    'title' => $userAnswer->answer->title,
                ];
            }),
            'created_at' => $this->first()->created_at,
            'updated_at' => $this->first()->updated_at,
        ];
    }
} 