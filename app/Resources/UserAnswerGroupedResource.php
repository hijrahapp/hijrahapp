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
        $data = [];
        if ($this->first()->module_id) {
            $data['question_id'] = $this->first()->question->id;
            $data['module_id'] = $this->first()->module_id;
            $data['module_name'] = $this->first()->module_name;

            $this->first()->question->setAttribute('module_id', $this->first()->module_id);
            $this->first()->question->setAttribute('module_name', $this->first()->module_name);
            $this->first()->question->setAttribute('questionId_moduleId', $this->first()->question->id . '_' . $this->first()->module_id);
        }

        $data['question'] = new QuestionResource($this->first()->question);

        $data['userAnswers'] = $this->map(function ($userAnswer) {
                    return [
                        'id' => $userAnswer->answer->id,
                        'title' => $userAnswer->answer->title,
                    ];
                });
        $data['created_at'] = $this->first()->created_at;
        $data['updated_at'] = $this->first()->updated_at;

        return $data;
    }
}
