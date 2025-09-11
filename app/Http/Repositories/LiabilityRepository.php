<?php

namespace App\Http\Repositories;

use App\Models\Liability;
use App\Models\UserLiabilityProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LiabilityRepository
{
    public function findById(int $liabilityId): ?Liability
    {
        return Liability::find($liabilityId);
    }

    public function getAll(): Collection
    {
        return Liability::all();
    }

    /**
     * Get liabilities that the user is eligible for based on their completed modules.
     */
    public function getUserLiabilities(int $userId): Collection
    {
        // Get all user's completed modules with their methodology and pillar contexts
        $userCompletedModules = DB::table('user_context_statuses as ucs')
            ->where('ucs.user_id', $userId)
            ->where('ucs.context_type', 'module')
            ->where('ucs.status', 'completed')
            ->get(['context_id as module_id', 'methodology_id', 'pillar_id']);

        if ($userCompletedModules->isEmpty()) {
            return Liability::query()->whereRaw('1 = 0')->get(); // Return empty Eloquent Collection
        }

        $eligibleLiabilities = collect();

        foreach ($userCompletedModules as $moduleData) {
            // Find liabilities linked to this specific module, methodology, and pillar combination
            $query = Liability::query()
                ->join('liability_module as lm', 'liabilities.id', '=', 'lm.liability_id')
                ->join('modules as m', 'lm.module_id', '=', 'm.id')
                ->join('methodology as mt', 'lm.methodology_id', '=', 'mt.id')
                ->leftJoin('pillars as p', 'lm.pillar_id', '=', 'p.id')
                ->where('lm.module_id', $moduleData->module_id)
                ->where('lm.methodology_id', $moduleData->methodology_id);

            // Add pillar condition if pillar exists
            if ($moduleData->pillar_id) {
                $query->where('lm.pillar_id', $moduleData->pillar_id);
            } else {
                $query->whereNull('lm.pillar_id');
            }

            $liabilities = $query->select([
                'liabilities.*',
                'lm.module_id as qualifying_module_id',
                'm.name as module_name',
                'mt.id as methodology_id',
                'mt.name as methodology_name',
                'p.id as pillar_id',
                'p.name as pillar_name',
            ])->get();

            $eligibleLiabilities = $eligibleLiabilities->merge($liabilities);
        }

        // Remove duplicates based on liability ID while preserving the first occurrence
        $uniqueLiabilities = $eligibleLiabilities->unique('id');

        // Convert back to Eloquent Collection
        return new Collection($uniqueLiabilities->values()->all());
    }

    /**
     * Get user's liability progress.
     */
    public function getUserLiabilityProgress(int $userId, int $liabilityId): ?UserLiabilityProgress
    {
        return UserLiabilityProgress::where('user_id', $userId)
            ->where('liability_id', $liabilityId)
            ->first();
    }

    /**
     * Update todo completion status for a user's liability.
     */
    public function updateTodoCompletion(int $userId, int $liabilityId, int $todoId, bool $isCompleted): bool
    {
        try {
            $progress = UserLiabilityProgress::firstOrCreate([
                'user_id' => $userId,
                'liability_id' => $liabilityId,
            ], [
                'completed_todos' => [],
                'is_completed' => false,
            ]);

            if ($isCompleted) {
                $progress->markTodoCompleted($todoId);
            } else {
                $progress->markTodoNotCompleted($todoId);
            }

            $progress->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Complete a liability if all todos are done.
     *
     * @return array{success: bool, error?: string}
     */
    public function completeLiability(int $userId, int $liabilityId): array
    {
        try {
            // First check if liability exists
            $liability = Liability::find($liabilityId);
            if (! $liability) {
                return ['success' => false, 'error' => 'liability_not_found'];
            }

            $progress = UserLiabilityProgress::where('user_id', $userId)
                ->where('liability_id', $liabilityId)
                ->first();

            if (! $progress || ! $progress->areAllTodosCompleted()) {
                return ['success' => false, 'error' => 'todos_not_completed'];
            }

            $progress->is_completed = true;
            $progress->save();

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'exception'];
        }
    }

    /**
     * Get liability with detailed information for a specific user.
     */
    public function getLiabilityWithUserProgress(int $liabilityId, int $userId): ?Liability
    {
        $liability = Liability::with(['userProgress' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->find($liabilityId);

        return $liability;
    }
}
