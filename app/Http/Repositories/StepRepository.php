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
            return ['success' => false, 'message' => __('messages.step_not_found')];
        }

        $progress = $step->getOrCreateProgressForUser($userId, $programId);

        // Handle quiz completion specially
        if ($step->type === 'quiz') {
            return $this->completeQuizStep($step, $progress, $userId, $data);
        }

        // Handle challenge completion validation
        if ($step->type === 'challenge') {
            return $this->completeChallengeStep($step, $progress, $userId, $data);
        }

        // Prepare completion data based on step type
        $completionData = $this->prepareCompletionData($step, $data);

        // Mark as completed
        $success = $progress->markAsCompleted($completionData);

        // Update program status based on step progress
        if ($success) {
            $this->updateProgramStatus($userId, $programId);
        }

        return [
            'success' => $success,
            'message' => $success ? __('messages.step_completed_successfully') : __('messages.error_completing_step'),
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
        $success = $progress->markAsStarted();

        // Update program status based on step progress
        if ($success) {
            $this->updateProgramStatus($userId, $programId);
        }

        return $success;
    }

    /**
     * Toggle individual challenge progress for a step
     */
    public function toggleChallengeProgress(int $userId, int $programId, int $stepId, int $challengeId, bool $isCompleted): array
    {
        $step = Step::find($stepId);
        if (! $step || $step->program_id !== $programId) {
            return ['success' => false, 'message' => __('messages.step_not_found')];
        }

        if ($step->type !== 'challenge') {
            return ['success' => false, 'message' => __('messages.invalid_step_type_for_challenge_progress')];
        }

        // Get total challenges count from the challenges JSON field
        $challenges = $step->challenges ?? [];
        $totalChallenges = is_array($challenges) ? count($challenges) : 0;

        if ($totalChallenges === 0) {
            return ['success' => false, 'message' => __('messages.no_challenges_found_for_step')];
        }

        // Validate challenge ID is within the valid range
        if ($challengeId < 1 || $challengeId > $totalChallenges) {
            return ['success' => false, 'message' => __('messages.invalid_challenge_id')];
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

        // Determine step status based on challenge completion
        // - If no challenges completed: not_started
        // - If all challenges completed: completed
        // - If at least one but not all completed: in_progress
        $stepStatus = "in_progress";
        if ($challengesDoneCount === $totalChallenges) {
            $stepStatus = 'completed';
        }

        // Update progress with new status
        $updateData = [
            'challenges_done' => json_encode($currentChallenges), // Use json_encode to bypass casting issue
            'percentage' => $percentage,
            'status' => $stepStatus,
        ];

        // Set timestamps based on status changes
        if ($stepStatus === 'completed') {
            // Just completed the step
            $updateData['completed_at'] = now();
        } else {
            $updateData['completed_at'] = null;
        }

        // Direct database update to bypass model casting issues
        $success = DB::table('user_step_progress')
            ->where('id', $progress->id)
            ->update($updateData + ['updated_at' => now()]);

        // Refresh the progress model to get updated data
        $progress->refresh();

        // Update program status based on step progress
        $this->updateProgramStatus($userId, $programId);

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
            return ['success' => false, 'message' => __('messages.no_questions_found_for_quiz_step')];
        }

        $userAnswers = $data['answers'] ?? [];
        $totalQuestions = $step->questions->count();
        $correctAnswers = 0;

        // Validate that all questions are answered
        if (count($userAnswers) !== $totalQuestions) {
            return ['success' => false, 'message' => __('messages.all_questions_must_be_answered')];
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
                return ['success' => false, 'message' => __('messages.invalid_question_for_step')];
            }

            // Check if answer is correct
            $isCorrect = $answerId === $correctAnswersMap[$questionId];
            if ($isCorrect) {
                $correctAnswers++;
            }

            $validatedAnswers[] = [
                'user_id' => $userId,
                'context_type' => 'step',
                'context_id' => $step->id,
                'question_id' => $questionId,
                'answer_id' => $answerId,
            ];
        }

        // Store user answers
        DB::transaction(function () use ($validatedAnswers, $userId, $step) {
            // Delete existing answers for this step
            \App\Models\UserAnswer::where('user_id', $userId)
                ->where('context_type', 'step')
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
            return ['success' => false, 'message' => __('messages.failed_to_save_quiz_results')];
        }

        // Update program status based on step progress
        $this->updateProgramStatus($userId, $step->program_id);

        return [
            'success' => true,
            'data' => [
                'score' => $score,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'percentage' => $percentage,
            ],
        ];
    }

    /**
     * Complete a challenge step with validation
     */
    private function completeChallengeStep(Step $step, UserStepProgress $progress, int $userId, array $data): array
    {
        // Get total challenges count from the challenges JSON field
        $challenges = $step->challenges ?? [];
        $totalChallenges = is_array($challenges) ? count($challenges) : 0;

        if ($totalChallenges === 0) {
            return [
                'success' => false,
                'message' => __('messages.no_challenges_found_for_step'),
            ];
        }

        // Get current completed challenges - use workaround for caching issue
        $currentChallenges = [];
        $rawChallenges = $progress->getRawOriginal('challenges_done');
        if (is_string($rawChallenges)) {
            $decoded = json_decode($rawChallenges, true);
            $currentChallenges = is_array($decoded) ? $decoded : [];
        } elseif (is_array($rawChallenges)) {
            $currentChallenges = $rawChallenges;
        }

        $completedChallengesCount = count($currentChallenges);

        // Validate that all challenges are completed
        if ($completedChallengesCount < $totalChallenges) {
            $remainingChallenges = $totalChallenges - $completedChallengesCount;

            return [
                'success' => false,
                'message' => __('messages.cannot_complete_step_challenges_remaining', ['remaining' => $remainingChallenges]),
                'data' => [
                    'completed_challenges' => $completedChallengesCount,
                    'total_challenges' => $totalChallenges,
                    'remaining_challenges' => $remainingChallenges,
                    'challenges_done' => $currentChallenges,
                ],
            ];
        }

        // All challenges are completed, proceed with step completion
        $completionData = [
            'challenges_done' => json_encode($currentChallenges),
            'percentage' => 100.00,
        ];

        // Mark as completed
        $success = $progress->markAsCompleted($completionData);

        // Update program status based on step progress
        // if ($success) {
            // $this->updateProgramStatus($userId, $step->program_id);
        // }

        return [
            'success' => $success,
            'message' => $success ? __('messages.challenge_step_completed_successfully') : __('messages.failed_to_complete_challenge_step'),
            'data' => [
                'completed_challenges' => $completedChallengesCount,
                'total_challenges' => $totalChallenges,
                'challenges_done' => $currentChallenges,
                'percentage' => 100.00,
            ],
        ];
    }

    /**
     * Update program status based on step progress
     */
    private function updateProgramStatus(int $userId, int $programId): void
    {
        // Get all steps for this program
        $totalSteps = Step::where('program_id', $programId)->count();

        if ($totalSteps === 0) {
            return;
        }

        // Get all step progress for this user and program
        $stepProgresses = UserStepProgress::where('user_id', $userId)
            ->where('program_id', $programId)
            ->get();

        // Count completed steps
        $completedSteps = $stepProgresses->where('status', 'completed')->count();

        $programStatus = "in_progress";
        if ($completedSteps > 0 && $completedSteps === $totalSteps) {
                $programStatus = 'completed';
        }

        // Update or create user program record
        $userProgram = \App\Models\UserProgram::where('user_id', $userId)
            ->where('program_id', $programId)
            ->first();

        if (! $userProgram) {
            // Create new user program record
            \App\Models\UserProgram::create([
                'user_id' => $userId,
                'program_id' => $programId,
                'status' => $programStatus,
                'started_at' => $programStatus !== 'not_started' ? now() : null,
                'completed_at' => $programStatus === 'completed' ? now() : null,
            ]);
        } else {
            // Update existing user program record
            $updateData = ['status' => $programStatus];

            // Set timestamps based on status changes
            if ($programStatus === 'completed') {
                $updateData['completed_at'] = now();
            } else {
                $updateData['completed_at'] = null;
            }

            $userProgram->update($updateData);
        }
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
