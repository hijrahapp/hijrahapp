<?php

namespace App\Http\Repositories;

use App\Models\Step;
use App\Models\UserStepProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StepRepository
{
    public function getAll(): Collection
    {
        return Step::with('program')->ordered()->get();
    }

    public function findById(int $stepId): ?Step
    {
        return Step::with('program')->find($stepId);
    }

    public function findByProgram(int $programId): Collection
    {
        return Step::forProgram($programId)
            ->with('program')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getStepsWithProgressForUser(int $userId, int $programId): Collection
    {
        return Step::forProgram($programId)
            ->with(['program', 'userProgress' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('id', 'asc')
            ->get();
    }

    public function findByProgramAndType(int $programId, string $type): Collection
    {
        return Step::forProgram($programId)
            ->ofType($type)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Complete a step for a user with type-specific data
     */
    public function completeStep(int $userId, int $programId, int $stepId, array $data = []): array
    {
        $step = Step::find($stepId);
        if (! $step || $step->program_id !== $programId) {
            return ['success' => false, 'message' => 'Step not found'];
        }

        $progress = $step->getOrCreateProgressForUser($userId, $programId);

        // Handle quiz completion specially
        if ($step->type === 'quiz') {
            return $this->completeQuizStep($step, $progress, $userId, $data);
        }

        // Prepare completion data based on step type
        $completionData = $this->prepareCompletionData($step, $data);

        // Mark as completed
        $success = $progress->markAsCompleted($completionData);

        return [
            'success' => $success,
            'message' => $success ? 'Step completed successfully' : 'Failed to complete step',
        ];
    }

    /**
     * Start a step for a user
     */
    public function startStep(int $userId, int $programId, int $stepId): bool
    {
        $step = Step::find($stepId);
        if (! $step || $step->program_id !== $programId) {
            return false;
        }

        $progress = $step->getOrCreateProgressForUser($userId, $programId);

        // Only start if not already completed
        if ($progress->isCompleted()) {
            return false;
        }

        // Mark as started
        return $progress->markAsStarted();
    }

    /**
     * Toggle individual challenge progress for a step
     */
    public function toggleChallengeProgress(int $userId, int $programId, int $stepId, int $challengeId, bool $isCompleted): array
    {
        $step = Step::find($stepId);
        if (! $step || $step->program_id !== $programId) {
            return ['success' => false, 'message' => 'Step not found'];
        }

        if ($step->type !== 'challenge') {
            return ['success' => false, 'message' => 'Invalid step type for challenge progress'];
        }

        // Get total challenges count from the challenges JSON field
        $challenges = $step->challenges ?? [];
        $totalChallenges = is_array($challenges) ? count($challenges) : 0;

        if ($totalChallenges === 0) {
            return ['success' => false, 'message' => 'No challenges found for this step'];
        }

        // Validate challenge ID is within the valid range
        if ($challengeId < 1 || $challengeId > $totalChallenges) {
            return ['success' => false, 'message' => 'Invalid challenge ID'];
        }

        // Get or create user progress
        $progress = $step->getOrCreateProgressForUser($userId, $programId);

        // Get current completed challenges - use workaround for caching issue
        $currentChallenges = [];
        $rawChallenges = $progress->getRawOriginal('challenges_done');
        if (is_string($rawChallenges)) {
            $decoded = json_decode($rawChallenges, true);
            $currentChallenges = is_array($decoded) ? $decoded : [];
        } elseif (is_array($rawChallenges)) {
            $currentChallenges = $rawChallenges;
        }

        // Update the challenges array based on completion status
        if ($isCompleted) {
            // Add challenge ID if not already present
            if (! in_array($challengeId, $currentChallenges)) {
                $currentChallenges[] = $challengeId;
            }
        } else {
            // Remove challenge ID if present
            $currentChallenges = array_filter($currentChallenges, function ($id) use ($challengeId) {
                return $id !== $challengeId;
            });
        }

        // Remove duplicates and sort
        $currentChallenges = array_unique(array_values($currentChallenges));
        sort($currentChallenges);

        // Calculate percentage
        $challengesDoneCount = count($currentChallenges);
        $percentage = min(100, round(($challengesDoneCount / $totalChallenges) * 100, 2));

        // Determine step status
        $stepStatus = $progress->status ?? 'not_started';
        if ($challengesDoneCount === 0) {
            $stepStatus = 'not_started';
        } elseif ($challengesDoneCount === $totalChallenges) {
            $stepStatus = 'completed';
        } else {
            $stepStatus = 'in_progress';
        }

        // Update progress with new status
        $updateData = [
            'challenges_done' => json_encode($currentChallenges), // Use json_encode to bypass casting issue
            'percentage' => $percentage,
            'status' => $stepStatus,
        ];

        // Set timestamps based on status
        if ($stepStatus === 'in_progress' && $progress->isNotStarted()) {
            $updateData['started_at'] = now();
        } elseif ($stepStatus === 'completed') {
            $updateData['completed_at'] = now();
        }

        // Direct database update to bypass model casting issues
        $success = DB::table('user_step_progress')
            ->where('id', $progress->id)
            ->update($updateData + ['updated_at' => now()]);

        // Refresh the progress model to get updated data
        $progress->refresh();

        return [
            'success' => true,
            'data' => [
                'challenge_id' => $challengeId,
                'is_completed' => $isCompleted,
                'challenges_done' => $currentChallenges,
                'challenges_done_count' => $challengesDoneCount,
                'total_challenges' => $totalChallenges,
                'percentage' => $percentage,
                'status' => $stepStatus,
            ],
        ];
    }

    /**
     * Complete a quiz step with answer validation and scoring
     */
    private function completeQuizStep(Step $step, UserStepProgress $progress, int $userId, array $data): array
    {
        // Load step questions with correct answers
        $step->load(['questions' => function ($query) {
            $query->orderBy('step_question.sequence');
        }]);

        if ($step->questions->isEmpty()) {
            return ['success' => false, 'message' => 'No questions found for this quiz step'];
        }

        $userAnswers = $data['answers'] ?? [];
        $totalQuestions = $step->questions->count();
        $correctAnswers = 0;

        // Validate that all questions are answered
        if (count($userAnswers) !== $totalQuestions) {
            return ['success' => false, 'message' => 'All questions must be answered'];
        }

        // Create a map of question_id => correct_answer_id for quick lookup
        $correctAnswersMap = $step->questions->pluck('pivot.correct_answer_id', 'id')->toArray();

        // Validate and score answers
        $validatedAnswers = [];
        foreach ($userAnswers as $answer) {
            $questionId = (int) $answer['question_id'];
            $answerId = (int) $answer['answer_id'];

            // Validate question exists in this step
            if (! isset($correctAnswersMap[$questionId])) {
                return ['success' => false, 'message' => 'Invalid question for this step'];
            }

            // Check if answer is correct
            $isCorrect = $answerId === $correctAnswersMap[$questionId];
            if ($isCorrect) {
                $correctAnswers++;
            }

            $validatedAnswers[] = [
                'user_id' => $userId,
                'context_type' => 'module',
                'context_id' => $step->id,
                'question_id' => $questionId,
                'answer_id' => $answerId,
            ];
        }

        // Store user answers
        DB::transaction(function () use ($validatedAnswers, $userId, $step) {
            // Delete existing answers for this step
            \App\Models\UserAnswer::where('user_id', $userId)
                ->where('context_type', 'module')
                ->where('context_id', $step->id)
                ->delete();

            // Insert new answers
            \App\Models\UserAnswer::insert($validatedAnswers);
        });

        // Calculate score and percentage
        $score = $correctAnswers;
        $percentage = round(($correctAnswers / $totalQuestions) * 100, 2);

        // Mark step as completed with score
        $success = $progress->markAsCompleted([
            'score' => $score,
            'percentage' => $percentage,
        ]);

        if (! $success) {
            return ['success' => false, 'message' => 'Failed to save quiz results'];
        }

        return [
            'score' => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'percentage' => $percentage,
        ];
    }

    /**
     * Prepare completion data based on step type
     */
    private function prepareCompletionData(Step $step, array $data): array
    {
        $completionData = [];

        switch ($step->type) {
            case 'journal':
                if (isset($data['thought'])) {
                    $completionData['thought'] = $data['thought'];
                }
                break;
        }

        return $completionData;
    }
}
