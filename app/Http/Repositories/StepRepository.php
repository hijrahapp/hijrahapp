<?php

namespace App\Http\Repositories;

use App\Models\Step;
use Illuminate\Database\Eloquent\Collection;

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
    public function completeStep(int $userId, int $programId, int $stepId, array $data = []): bool
    {
        $step = Step::find($stepId);
        if (! $step || $step->program_id !== $programId) {
            return false;
        }

        $progress = $step->getOrCreateProgressForUser($userId, $programId);

        // Prepare completion data based on step type
        $completionData = $this->prepareCompletionData($step, $data);

        // Mark as completed
        return $progress->markAsCompleted($completionData);
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
     * Update challenge progress for a step
     */
    public function updateChallengeProgress(int $userId, int $programId, int $stepId, int $challengesDone): array
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

        // Get or create user progress
        $progress = $step->getOrCreateProgressForUser($userId, $programId);

        // Calculate percentage
        $percentage = min(100, round(($challengesDone / $totalChallenges) * 100, 2));

        // Update progress
        $success = $progress->updateProgress($percentage, [
            'challenges_done' => min($challengesDone, $totalChallenges),
        ]);

        return [
            'success' => $success,
            'data' => [
                'challenges_done' => min($challengesDone, $totalChallenges),
                'total_challenges' => $totalChallenges,
                'percentage' => $percentage,
                'status' => $progress->status,
            ],
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

            case 'quiz':
                if (isset($data['score'])) {
                    $completionData['score'] = (int) $data['score'];
                }
                break;

            case 'challenge':
                if (isset($data['challenges_done'])) {
                    $completionData['challenges_done'] = (int) $data['challenges_done'];
                }
                if (isset($data['percentage'])) {
                    $completionData['percentage'] = (float) $data['percentage'];
                }
                break;
        }

        return $completionData;
    }
}
