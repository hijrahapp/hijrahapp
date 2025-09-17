<?php

namespace App\Resources;

use App\Services\ContextStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Lang;

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
            'challenges' => $this->formatChallenges($request),
        ];

        // Add questions for quiz-type steps
        if ($this->type === 'quiz' && $this->relationLoaded('questions')) {
            $userAnswers = $this->relationLoaded('userAnswers') ? $this->userAnswers : collect();
            $hasUser = $request->authUserId !== null;

            $array['questions'] = $this->questions->map(function ($question) use ($userAnswers, $hasUser) {
                $questionUserAnswers = $userAnswers->get($question->id, collect());

                $questionData = [
                    'id' => $question->id,
                    'title' => $question->title,
                    'answers' => $question->answers->map(function ($answer) use ($question) {
                        $lookupKey = 'lookups.' . $answer->title;
                        $translatedTitle = Lang::has($lookupKey) ? __($lookupKey) : $answer->title;
                        return [
                            'id' => $answer->id,
                            'text' => $translatedTitle,
                            'is_correct' => $answer->id == $question->pivot->correct_answer_id,
                        ];
                    })->toArray(),
                ];

                // Only include user_answers if user is authenticated
                if ($hasUser) {
                    $questionData['user_answers'] = $questionUserAnswers->map(function ($userAnswer) {
                        return [
                            'id' => $userAnswer->answer->id,
                            'text' => $userAnswer->answer->title,
                        ];
                    })->toArray();
                }

                return $questionData;
            })->toArray();
        }

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
            }
        }

        return $array;
    }

    /**
     * Format challenges as array of objects with id and text
     */
    private function formatChallenges($request): array
    {
        if (! $this->challenges || ! is_array($this->challenges)) {
            return [];
        }

        // Get completed challenge IDs from user progress
        $completedChallengeIds = [];
        if ($request->authUserId) {
            $progress = $this->progressForUser($request->authUserId);
            if ($progress) {
                $completedChallengeIds = $progress->getChallengesDoneArray();
            }
        }

        return collect($this->challenges)->map(function ($challenge, $index) use ($completedChallengeIds) {
            $challengeId = $index + 1;
            $timeType = __('lookups.'. str_replace('s', '', $this->time_type));
            $timeToFinish =  __('lookups.'. $challengeId);
            return [
                'id' => $challengeId,
                'title' => $timeType . ' ' . $timeToFinish,
                'text' => $challenge,
                'is_completed' => in_array($challengeId, $completedChallengeIds),
            ];
        })->toArray();
    }
}
