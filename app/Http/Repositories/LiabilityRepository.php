<?php

namespace App\Http\Repositories;

use App\Models\Liability;
use App\Models\Methodology;
use App\Models\Pillar;
use App\Models\UserContextStatus;
use App\Models\UserLiabilityProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

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
        // Get user's completed modules using Eloquent model
        $userCompletedModulesQuery = UserContextStatus::where('user_id', $userId)
            ->where('context_type', 'module')
            ->where('status', 'completed');

        // Apply filters
        if (! empty($methodologyIds)) {
            $userCompletedModulesQuery->whereIn('methodology_id', $methodologyIds);
        }

        if (! empty($moduleIds)) {
            $userCompletedModulesQuery->whereIn('context_id', $moduleIds);
        }

        $userCompletedModules = $userCompletedModulesQuery->get(['context_id as module_id', 'methodology_id', 'pillar_id']);

        if ($userCompletedModules->isEmpty()) {
            return collect(); // Return empty collection
        }

        // Build constraints for liability query based on user's completed modules
        $moduleConstraints = $userCompletedModules->map(function ($moduleData) {
            return [
                'module_id' => $moduleData->module_id,
                'methodology_id' => $moduleData->methodology_id,
                'pillar_id' => $moduleData->pillar_id,
            ];
        });

        // Get eligible liabilities using a single optimized query with eager loading
        $liabilitiesQuery = Liability::with([
            'modules' => function ($query) use ($moduleConstraints) {
                $query->with(['methodologies:id,name', 'pillars:id,name']);
            }
        ])
            ->where('active', true)
            ->whereHas('modules', function ($query) use ($moduleConstraints) {
                $query->where(function ($innerQuery) use ($moduleConstraints) {
                    foreach ($moduleConstraints as $constraint) {
                        $innerQuery->orWhere(function ($constraintQuery) use ($constraint) {
                            $constraintQuery->where('liability_module.module_id', $constraint['module_id'])
                                ->where('liability_module.methodology_id', $constraint['methodology_id']);

                            if ($constraint['pillar_id']) {
                                $constraintQuery->where('liability_module.pillar_id', $constraint['pillar_id']);
                            } else {
                                $constraintQuery->whereNull('liability_module.pillar_id');
                            }
                        });
                    }
                });
            });

        $liabilities = $liabilitiesQuery->get();

        // Get user progress data in bulk to avoid N+1 queries
        $userProgressData = collect();
        if (! empty($status) && $liabilities->isNotEmpty()) {
            $userProgressData = UserLiabilityProgress::where('user_id', $userId)
                ->whereIn('liability_id', $liabilities->pluck('id'))
                ->get()
                ->keyBy('liability_id');
        }

        // Process liabilities and add qualifying module information
        $processedLiabilities = $liabilities->map(function ($liability) use ($moduleConstraints, $userProgressData, $status, $userId) {
            // Find the qualifying module for this liability
            foreach ($liability->modules as $module) {
                $pivot = $module->pivot;

                // Check if this module matches any of the user's completed modules
                $matchingConstraint = $moduleConstraints->first(function ($constraint) use ($pivot) {
                    return $constraint['module_id'] == $pivot->module_id &&
                           $constraint['methodology_id'] == $pivot->methodology_id &&
                           $constraint['pillar_id'] == $pivot->pillar_id;
                });

                if ($matchingConstraint) {
                    // Get methodology and pillar data
                    $methodology = Methodology::find($pivot->methodology_id);
                    $pillar = $pivot->pillar_id ? Pillar::find($pivot->pillar_id) : null;

                    // Add qualifying module data to liability
                    $liability->qualifying_module_id = $module->id;
                    $liability->module_name = $module->name;
                    $liability->methodology_id = $pivot->methodology_id;
                    $liability->methodology_name = $methodology?->name;
                    $liability->pillar_id = $pivot->pillar_id;
                    $liability->pillar_name = $pillar?->name;
                    break; // Use first qualifying module
                }
            }

            // Add user progress data if status filtering is needed
            if (! empty($status)) {
                $userProgress = $userProgressData->get($liability->id);
                $isCompleted = $userProgress ? $userProgress->is_completed : false;
                $hasProgress = $userProgress && $userProgress->getCompletedTodosCount() > 0;

                $liabilityStatus = 'not_started';
                if ($isCompleted) {
                    $liabilityStatus = 'completed';
                } elseif ($hasProgress) {
                    $liabilityStatus = 'in_progress';
                }

                $liability->user_status = $liabilityStatus;
            }

            return $liability;
        });

        // Apply status filter if provided
        if (! empty($status)) {
            $processedLiabilities = $processedLiabilities->filter(function ($liability) use ($status) {
                return in_array($liability->user_status, $status);
            });
        }

        return $processedLiabilities->values();
    }

    /**
     * Get unique methodologies and modules from user liabilities for filtering.
     */
    public function getUserLiabilitiesFilters(int $userId): array
    {
        // Reuse the getUserLiabilities method to get actual user liabilities
        $userLiabilities = $this->getUserLiabilities($userId);

        // Extract filters from user liabilities
        $filters = $this->extractFiltersFromLiabilities($userLiabilities);

        return [
            'methodologies' => $filters['methodologies'],
            'modules' => $filters['modules'],
            'statuses' => ['not_started', 'in_progress', 'completed'],
        ];
    }

    /**
     * Extract unique methodologies and modules from liabilities with qualifying module data.
     */
    private function extractFiltersFromLiabilities(BaseCollection $liabilities): array
    {
        $methodologies = collect();
        $modules = collect();

        foreach ($liabilities as $liability) {
            if (isset($liability->methodology_id) && isset($liability->methodology_name)) {
                // Add methodology if not already added
                if (! $methodologies->contains('id', $liability->methodology_id)) {
                    $methodologies->push([
                        'id' => $liability->methodology_id,
                        'name' => $liability->methodology_name,
                    ]);
                }
            }

            if (isset($liability->qualifying_module_id) && isset($liability->module_name)) {
                // Add module if not already added
                if (! $modules->contains('id', $liability->qualifying_module_id)) {
                    $modules->push([
                        'id' => $liability->qualifying_module_id,
                        'name' => $liability->module_name,
                    ]);
                }
            }
        }

        return [
            'methodologies' => $methodologies->sortBy('name')->values()->toArray(),
            'modules' => $modules->sortBy('name')->values()->toArray(),
        ];
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

}
