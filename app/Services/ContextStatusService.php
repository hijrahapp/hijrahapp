<?php

namespace App\Services;

use App\Models\Pillar;
use App\Models\UserContextStatus;

class ContextStatusService
{
    /**
     * Determine completion status for a methodology based on answered questions
     * Returns: not_started | in_progress | completed
     */
    public function getMethodologyStatus(int $userId, int $methodologyId): string
    {
        $methodology = \App\Models\Methodology::find($methodologyId);
        if (! $methodology) {
            return 'not_started';
        }

        // Get only direct methodology questions (not pillar or module questions)
        $methodologyQuestionIds = $methodology->questions()->pluck('questions.id');
        $totalQuestionsCount = $methodologyQuestionIds->count();

        if ($totalQuestionsCount === 0) {
            return 'not_started';
        }

        // Count how many methodology questions the user has answered
        $answeredQuestionsCount = \App\Models\UserAnswer::where('user_id', $userId)
            ->where('context_type', 'methodology')
            ->where('context_id', $methodologyId)
            ->whereIn('question_id', $methodologyQuestionIds)
            ->distinct('question_id')
            ->count('question_id');

        if ($answeredQuestionsCount === 0) {
            return 'not_started';
        }

        if ($answeredQuestionsCount < $totalQuestionsCount) {
            return 'in_progress';
        }

        return 'completed';
    }

    /**
     * Determine completion status for a pillar for a given user in a methodology
     * Returns: not_started | in_progress | completed
     */
    public function getPillarStatus(int $userId, int $pillarId, int $methodologyId): string
    {
        $pillar = Pillar::find($pillarId);
        if (! $pillar) {
            return 'not_started';
        }

        $modules = $pillar->modulesForMethodology($methodologyId)->get();
        if ($modules->isEmpty()) {
            return 'not_started';
        }

        $moduleIds = $modules->pluck('id')->all();

        $statuses = UserContextStatus::where('user_id', $userId)
            ->where('context_type', 'module')
            ->whereIn('context_id', $moduleIds)
            ->where('methodology_id', $methodologyId)
            ->where('pillar_id', $pillarId)
            ->pluck('status', 'context_id');

        if ($statuses->isEmpty()) {
            return 'not_started';
        }

        if ($statuses->contains('in_progress')) {
            return 'in_progress';
        }

        if ($statuses->count() < count($moduleIds)) {
            return 'in_progress';
        }

        return 'completed';
    }

    /**
     * Determine completion status for a module for a given user in a methodology (and pillar when applicable)
     * Returns: not_started | in_progress | completed
     */
    public function getModuleStatus(int $userId, int $moduleId, int $methodologyId, ?int $pillarId = null): string
    {
        $statusQuery = UserContextStatus::where('user_id', $userId)
            ->where('context_type', 'module')
            ->where('context_id', $moduleId)
            ->where('methodology_id', $methodologyId);

        if ($pillarId) {
            $statusQuery->where('pillar_id', $pillarId);
        } else {
            $statusQuery->where('pillar_id', 0);
        }

        $status = $statusQuery->value('status');
        if (! $status) {
            return 'not_started';
        }

        return $status;
    }

    /**
     * Determine completion status for a program for a given user
     * Returns: not_started | in_progress | completed
     */
    public function getProgramStatus(int $userId, int $programId): string
    {
        $userProgram = \App\Models\Program::find($programId)
            ->users()
            ->wherePivot('user_id', $userId)
            ->first();

        if (! $userProgram) {
            return 'not_started';
        }

        return $userProgram->pivot->status;
    }

    /**
     * Determine completion status for a step for a given user
     * Returns: not_started | in_progress | completed
     */
    public function getStepStatus(int $userId, int $stepId, int $programId): string
    {
        $userStepProgress = \App\Models\UserStepProgress::forUserAndProgram($userId, $programId)
            ->where('step_id', $stepId)
            ->first();

        if (! $userStepProgress) {
            return 'not_started';
        }

        return $userStepProgress->status ?? 'not_started';
    }

    /**
     * Get count of completed steps for a user in a program
     */
    public function getCompletedStepsCount(int $userId, int $programId): int
    {
        return \App\Models\UserStepProgress::forUserAndProgram($userId, $programId)
            ->completed()
            ->count();
    }

    /**
     * Determine completion status for a liability for a given user
     * Returns: not_started | in_progress | completed
     */
    public function getLiabilityStatus(int $userId, int $liabilityId): string
    {
        $liability = \App\Models\Liability::find($liabilityId);
        if (! $liability) {
            return 'not_started';
        }

        $userProgress = \App\Models\UserLiabilityProgress::where('user_id', $userId)
            ->where('liability_id', $liabilityId)
            ->first();

        if (! $userProgress) {
            return 'not_started';
        }

        // If liability is marked as completed
        if ($userProgress->is_completed) {
            return 'completed';
        }

        // Check if any todos are completed (in_progress)
        $completedTodos = $userProgress->completed_todos ?? [];
        if (count($completedTodos) > 0) {
            return 'in_progress';
        }

        return 'not_started';
    }
}
