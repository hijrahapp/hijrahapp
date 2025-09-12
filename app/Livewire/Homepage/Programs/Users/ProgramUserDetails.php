<?php

namespace App\Livewire\Homepage\Programs\Users;

use App\Models\FeedbackForm;
use App\Models\Program;
use App\Models\ProgramFeedback;
use App\Models\Step;
use App\Models\User;
use App\Models\UserStepProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.index')]
class ProgramUserDetails extends Component
{
    public Program $program;

    public User $user;

    public function mount(Program $program, User $user)
    {
        $this->program = $program;
        $this->user = $user;
    }

    #[Computed]
    public function steps()
    {
        return $this->program->stepsList()
            ->with(['questions'])
            ->get();
    }

    #[Computed]
    public function userStepProgress()
    {
        return UserStepProgress::where('user_id', $this->user->id)
            ->where('program_id', $this->program->id)
            ->get()
            ->keyBy('step_id');
    }

    #[Computed]
    public function userStepAnswers()
    {
        return \App\Models\UserAnswer::where('user_id', $this->user->id)
            ->where('context_type', 'step')
            ->whereIn('context_id', $this->steps->pluck('id'))
            ->with(['question', 'answer'])
            ->get()
            ->groupBy('context_id');
    }

    #[Computed]
    public function userProgram()
    {
        return $this->user->userPrograms()
            ->where('program_id', $this->program->id)
            ->first();
    }

    #[Computed]
    public function userFeedback()
    {
        return ProgramFeedback::where('user_id', $this->user->id)
            ->where('program_id', $this->program->id)
            ->latest('submitted_at')
            ->first();
    }

    #[Computed]
    public function feedbackForm()
    {
        $feedback = $this->userFeedback;

        if (! $feedback) {
            return null;
        }

        return FeedbackForm::getByVersion($feedback->form_version, 'ar')
            ?? FeedbackForm::getByVersion($feedback->form_version, 'en');
    }

    #[Computed]
    public function programProgress()
    {
        $totalSteps = $this->steps->count();
        $completedSteps = $this->userStepProgress->where('status', 'completed')->count();
        $inProgressSteps = $this->userStepProgress->where('status', 'in_progress')->count();
        $notStartedSteps = $this->userStepProgress->where('status', 'not_started')->count();

        if ($totalSteps === 0) {
            return [
                'total_steps' => 0,
                'completed_steps' => 0,
                'in_progress_steps' => 0,
                'not_started_steps' => 0,
                'completion_percentage' => 0,
                'status' => 'not_started',
            ];
        }

        $completionPercentage = ($completedSteps / $totalSteps) * 100;

        // $status = 'not_started';
        // if ($completedSteps > 0) {
        //     $status = $completedSteps === $totalSteps ? 'completed' : 'in_progress';
        // } elseif ($inProgressSteps > 0) {
        //     $status = 'in_progress';
        // }

        return [
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'in_progress_steps' => $inProgressSteps,
            'not_started_steps' => $notStartedSteps,
            'completion_percentage' => round($completionPercentage, 2),
            'status' => $this->userProgram->status,
        ];
    }

    public function getStepProgress(int $stepId): ?UserStepProgress
    {
        return $this->userStepProgress->get($stepId);
    }

    public function getStepStatus(int $stepId): string
    {
        $progress = $this->getStepProgress($stepId);

        return $progress ? $progress->status : 'not_started';
    }

    public function getStepData(int $stepId): array
    {
        $step = $this->steps->firstWhere('id', $stepId);
        $progress = $this->getStepProgress($stepId);

        if (! $step) {
            return [];
        }

        $data = [
            'step' => $step,
            'progress' => $progress,
            'status' => $this->getStepStatus($stepId),
            'user_input' => null,
            'score' => null,
            'percentage' => null,
        ];

        // Handle different step types
        switch ($step->type) {
            case 'journal':
                $data['user_input'] = $progress?->thought;
                break;

            case 'quiz':
                if ($progress) {
                    $data['score'] = $progress->score;
                    $data['percentage'] = $progress->percentage;
                    $data['user_input'] = $this->getQuizAnswers($step, $progress);
                }
                break;

            case 'challenge':
                if ($progress) {
                    $data['challenges_completed'] = $progress->challenges_done;
                    $data['percentage'] = $progress->percentage;
                    $data['user_input'] = $this->getChallengeProgress($step, $progress);
                }
                break;

            case 'article':
            case 'advice':
            case 'daily_mission':
            case 'video':
            case 'audio':
            case 'book':
                // These types don't have user input
                $data['user_input'] = 'No user input required';
                break;
        }

        return $data;
    }

    private function getQuizAnswers(Step $step, UserStepProgress $progress): array
    {
        $userAnswers = $this->userStepAnswers->get($step->id, collect());
        $questions = $step->questions;

        $quizData = [
            'score' => $progress->score,
            'percentage' => $progress->percentage,
            'questions_count' => $questions->count(),
            'answered_count' => $userAnswers->count(),
            'questions' => [],
        ];

        // Get detailed question and answer information
        foreach ($questions as $question) {
            $userAnswer = $userAnswers->firstWhere('question_id', $question->id);
            $correctAnswer = $question->pivot->correct_answer_id ?? null;

            $quizData['questions'][] = [
                'question' => $question,
                'user_answer' => $userAnswer?->answer,
                'correct_answer_id' => $correctAnswer,
                'is_correct' => $userAnswer && $userAnswer->answer_id == $correctAnswer,
                'answered_at' => $userAnswer?->created_at,
            ];
        }

        return $quizData;
    }

    private function getChallengeProgress(Step $step, UserStepProgress $progress): array
    {
        $challenges = $step->challenges ?? [];
        $completedChallenges = $progress->challenges_done ?? [];

        // Convert to array if it's JSON
        if (is_string($completedChallenges)) {
            $completedChallenges = json_decode($completedChallenges, true) ?? [];
        }

        $challengeDetails = [];
        foreach ($challenges as $index => $challenge) {
            $challengeDetails[] = [
                'index' => $index + 1,
                'title' => $challenge,
                'is_completed' => in_array($index + 1, $completedChallenges),
            ];
        }

        return [
            'total_challenges' => count($challenges),
            'completed_challenges' => count($completedChallenges),
            'challenges' => $challenges,
            'completed' => $completedChallenges,
            'challenge_details' => $challengeDetails,
            'percentage' => $progress->percentage,
        ];
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-50 text-green-600',
            'in_progress' => 'bg-yellow-50 text-yellow-600',
            'not_started' => 'bg-gray-100 text-gray-800',
            'skipped' => 'bg-orange-50 text-orange-600',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'not_started' => 'Not Started',
            'skipped' => 'Skipped',
            default => 'Unknown',
        };
    }

    public function getStepTypeDisplayName(string $type): string
    {
        return match ($type) {
            'journal' => 'Journal',
            'article' => 'Article',
            'advice' => 'Advice',
            'daily_mission' => 'Daily Mission',
            'quiz' => 'Quiz',
            'video' => 'Video',
            'audio' => 'Audio',
            'book' => 'Book',
            'challenge' => 'Challenge',
            default => ucfirst($type),
        };
    }

    public function getFeedbackResponseDisplay(array $question, mixed $response): string
    {
        if ($response === null) {
            return 'No response provided';
        }

        switch ($question['type']) {
            case 'rating':
                return $response.' / '.$question['max_value'];

            case 'single_choice':
                $option = collect($question['options'])->firstWhere('value', $response);

                return $option['label'] ?? $response;

            case 'multiple_choice':
                if (! is_array($response)) {
                    return 'Invalid response format';
                }
                $labels = collect($question['options'])
                    ->whereIn('value', $response)
                    ->pluck('label')
                    ->toArray();

                return implode(', ', $labels);

            case 'text':
                return $response;

            default:
                return (string) $response;
        }
    }

    public function render()
    {
        return view('livewire.homepage.programs.users.program-user-details');
    }
}
