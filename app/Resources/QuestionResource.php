<?php

namespace App\Resources;

use App\Enums\QuestionType;
use App\Traits\HasTagTitles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    use HasTagTitles;

    public function toArray(Request $request): array
    {
        $answers = $this->answers->map(function ($answer) {
            $answerData = [
                'id' => $answer->id,
                'title' => $answer->title,
            ];

            // Add weight if available for this answer in this context
            if (isset($this->answer_weights) && $this->answer_weights->has($answer->id)) {
                $answerData['weight'] = $this->answer_weights->get($answer->id)->weight;
            }

            return $answerData;
        });

        $type = 'single';
        if($this->type == QuestionType::MCQMultiple) {
            $type = 'multiple';
        }

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $type,
            'tags' => $this->getTagTitles($this->tags),
            'answers' => $answers,
        ];

        if($this->module_id) {
            $data['module_id'] = $this->module_id;
            $data['module_name'] = $this->module_name;
        }

        // Add question weight if available (from pivot)
        if (isset($this->pivot) && isset($this->pivot->weight)) {
            $data['weight'] = $this->pivot->weight;
        }

        return $data;
    }
}
