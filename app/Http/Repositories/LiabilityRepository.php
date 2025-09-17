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
    public function getUserLiabilities(int $userId, array $methodologyIds = [], array $moduleIds = [], array $status = []): Collection
    {
        // Get all user's completed modules with their methodology and pillar contexts
        $userModuleQuery = DB::table('user_context_statuses as ucs')
            ->where('ucs.user_id', $userId)
            ->where('ucs.context_type', 'module')
            ->where('ucs.status', 'completed');

        // Apply methodology filter
        if (! empty($methodologyIds)) {
            $userModuleQuery->whereIn('ucs.methodology_id', $methodologyIds);
        }

        // Apply module filter
        if (! empty($moduleIds)) {
            $userModuleQuery->whereIn('ucs.context_id', $moduleIds);
        }

        $userCompletedModules = $userModuleQuery->get(['context_id as module_id', 'methodology_id', 'pillar_id']);

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
                ->where('lm.methodology_id', $moduleData->methodology_id)
                ->where('liabilities.active', true);

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

        // Apply status filter if provided
        if (! empty($status) && $uniqueLiabilities->isNotEmpty()) {
            $uniqueLiabilities = $uniqueLiabilities->filter(function ($liability) use ($status, $userId) {
                $userProgress = UserLiabilityProgress::query()
                ->where('user_id', $userId)
                ->where('liability_id', $liability->id)
                ->first();
                $isCompleted = $userProgress ? $userProgress->is_completed : false;
                $hasProgress = $userProgress && $userProgress->getCompletedTodosCount() > 0;

                $liabilityStatus = 'not_started';
                if ($isCompleted) {
                    $liabilityStatus = 'completed';
                } elseif ($hasProgress) {
                    $liabilityStatus = 'in_progress';
                }

                return in_array($liabilityStatus, $status);
            });
        }

        // Convert back to Eloquent Collection
        return new Collection($uniqueLiabilities->values()->all());
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

            // // Check if all todos are completed and update liability status
            // if ($progress->areAllTodosCompleted()) {
            //     $progress->is_completed = true;
            //     $progress->save();
            // } else {
            //     // If not all todos are completed, ensure is_completed is false
            //     $progress->is_completed = false;
            //     $progress->save();
            // }

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
     * Automatically creates or updates user progress to "in_progress" when accessed.
     */
    public function getLiabilityWithUserProgress(int $liabilityId, int $userId): ?Liability
    {
        $liability = Liability::with(['userProgress' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->find($liabilityId);

        if (! $liability) {
            return null;
        }

        // Create or update user progress to "in_progress" when liability is accessed
        // $progress = UserLiabilityProgress::firstOrCreate([
        //     'user_id' => $userId,
        //     'liability_id' => $liabilityId,
        // ], [
        //     'completed_todos' => [],
        //     'is_completed' => false,
        // ]);

        // If the liability is not completed and user is accessing it, ensure it's marked as in_progress
        // if (! $progress->is_completed) {
            // The progress record already exists with is_completed = false, which means it's in_progress
            // We don't need to update anything here as the status is already correct
        // }

        // Reload the liability with the updated progress
        $liability->load(['userProgress' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }]);

        return $liability;
    }

    /**
     * Get unique methodologies and modules from user liabilities for filtering.
     */
    public function getUserLiabilitiesFilters(int $userId): array
    {
        // Get unique methodologies and modules from user's eligible liabilities
        $methodologyAndModules = DB::table('user_context_statuses as ucs')
            ->join('liability_module as lm', function ($join) {
                $join->on('ucs.context_id', '=', 'lm.module_id')
                    ->on('ucs.methodology_id', '=', 'lm.methodology_id');
            })
            ->leftJoin('methodology as m', 'lm.methodology_id', '=', 'm.id')
            ->leftJoin('modules as mod', 'lm.module_id', '=', 'mod.id')
            ->where('ucs.user_id', $userId)
            ->where('ucs.context_type', 'module')
            ->where('ucs.status', 'completed')
            ->whereNotNull('m.id')
            ->whereNotNull('mod.id')
            ->select(
                'm.id as methodology_id',
                'm.name as methodology_name',
                'mod.id as module_id',
                'mod.name as module_name'
            )
            ->distinct()
            ->get();

        // Group by methodology and module
        $methodologies = $methodologyAndModules->groupBy('methodology_id')
            ->map(fn ($group) => [
                'id' => $group->first()->methodology_id,
                'name' => $group->first()->methodology_name,
            ])
            ->values();

        $modules = $methodologyAndModules->groupBy('module_id')
            ->map(fn ($group) => [
                'id' => $group->first()->module_id,
                'name' => $group->first()->module_name,
            ])
            ->values();

        return [
            'methodologies' => $methodologies->toArray(),
            'modules' => $modules->toArray(),
            'statuses' => ['not_started', 'in_progress', 'completed'],
        ];
    }
}
