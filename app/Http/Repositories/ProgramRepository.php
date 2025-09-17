<?php

namespace App\Http\Repositories;

use App\Models\Program;
use App\Models\UserProgram;
use App\Models\UserStepProgress;
use App\Services\ResultCalculationOptimizedService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgramRepository
{
    public function __construct(
        private readonly ResultCalculationOptimizedService $resultCalculationService
    ) {}

    public function findById(int $programId): ?Program
    {
        return Program::find($programId);
    }

    public function getAll(): Collection
    {
        return Program::all();
    }

    /**
     * Get programs suggested for user based on their completed modules and scores.
     */
    public function getSuggestedPrograms(int $userId, array $methodologyIds = [], array $moduleIds = [], array $statuses = []): Collection
    {
        // Build the base query using Eloquent relationships
        $programsQuery = Program::with([
            'modules' => function ($query) use ($methodologyIds, $moduleIds) {
                // Apply filters at the relationship level
                if (! empty($methodologyIds)) {
                    $query->whereIn('program_module.methodology_id', $methodologyIds);
                }
                if (! empty($moduleIds)) {
                    $query->whereIn('program_module.module_id', $moduleIds);
                }
            }
        ])
            ->where('active', true)
            ->withCount('stepsList as steps_count');

        // Optionally exclude programs user is already enrolled in
        // Uncomment if needed: ->whereDoesntHave('users', fn($q) => $q->where('user_id', $userId))

        $programs = $programsQuery->get();

        // Get user program statuses in bulk to avoid N+1 queries
        $userProgramStatuses = UserProgram::where('user_id', $userId)
            ->whereIn('program_id', $programs->pluck('id'))
            ->get()
            ->keyBy('program_id');

        // Get user step progress data in bulk if status filtering is needed
        $userStepProgressData = collect();
        if (! empty($statuses) && $programs->isNotEmpty()) {
            $userStepProgressData = \App\Models\UserStepProgress::where('user_id', $userId)
                ->whereIn('program_id', $programs->pluck('id'))
                ->get()
                ->groupBy('program_id');
        }

        // Get methodologies and pillars data in bulk
        $methodologyIds_toLoad = [];
        $pillarIds_toLoad = [];

        foreach ($programs as $program) {
            foreach ($program->modules as $module) {
                $methodologyIds_toLoad[] = $module->pivot->methodology_id;
                if ($module->pivot->pillar_id) {
                    $pillarIds_toLoad[] = $module->pivot->pillar_id;
                }
            }
        }

        // Load methodologies and pillars in bulk
        $methodologies = collect();
        $pillars = collect();

        if (! empty($methodologyIds_toLoad)) {
            $methodologies = \App\Models\Methodology::whereIn('id', array_unique($methodologyIds_toLoad))
                ->get()
                ->keyBy('id');
        }

        if (! empty($pillarIds_toLoad)) {
            $pillars = \App\Models\Pillar::whereIn('id', array_unique($pillarIds_toLoad))
                ->get()
                ->keyBy('id');
        }

        $qualifiedPrograms = collect();

        foreach ($programs as $program) {
            $qualifyingModule = null;

            // Find the first qualifying module for this program
            foreach ($program->modules as $module) {
                $pivot = $module->pivot;
                $moduleResult = null;

                if ($pivot->pillar_id) {
                    $moduleResult = $this->resultCalculationService->calculateModuleResult(
                        $userId,
                        $module->id,
                        $pivot->methodology_id,
                        $pivot->pillar_id
                    );
                } else {
                    $moduleResult = $this->resultCalculationService->calculateModuleResult(
                        $userId,
                        $module->id,
                        $pivot->methodology_id,
                        null
                    );
                }

                if ($moduleResult && isset($moduleResult['percentage'])) {
                    $userScore = $moduleResult['percentage'];

                    if ($userScore >= $pivot->min_score && $userScore <= $pivot->max_score) {
                        // Get methodology and pillar from loaded collections
                        $methodology = $methodologies->get($pivot->methodology_id);
                        $pillar = $pivot->pillar_id ? $pillars->get($pivot->pillar_id) : null;

                        $qualifyingModule = [
                            'id' => $module->id,
                            'name' => $module->name,
                            'description' => $module->description,
                            'definition' => $module->definition,
                            'objectives' => $module->objectives,
                            'pillar' => $pillar ? [
                                'id' => $pillar->id,
                                'name' => $pillar->name,
                            ] : null,
                            'methodology' => [
                                'id' => $pivot->methodology_id,
                                'name' => $methodology?->name,
                            ],
                            'user_score' => $userScore,
                            'required_range' => [
                                'min' => $pivot->min_score,
                                'max' => $pivot->max_score,
                            ],
                        ];
                        break; // Use first qualifying module
                    }
                }
            }

            // Only include programs with qualifying modules
            if ($qualifyingModule) {
                // Add qualifying module data to the program model
                $program->qualifying_module = $qualifyingModule;

                // Add user program status if exists
                $userProgram = $userProgramStatuses->get($program->id);

                // Calculate status based on step progress if status filtering is needed
                if (! empty($statuses)) {
                    $stepProgress = $userStepProgressData->get($program->id, collect());
                    $completedSteps = $stepProgress->where('status', 'completed')->count();
                    $inProgressSteps = $stepProgress->where('status', 'in_progress')->count();

                    $programStatus = 'not_started';
                    if ($userProgram && $userProgram->status === 'completed') {
                        $programStatus = 'completed';
                    } elseif ($completedSteps > 0 || $inProgressSteps > 0) {
                        $programStatus = 'in_progress';
                    }

                    $program->user_program_status = $programStatus;
                } else {
                    $program->user_program_status = $userProgram?->status ?? 'not_started';
                }

                $program->started_at = $userProgram?->started_at;
                $program->completed_at = $userProgram?->completed_at;

                $qualifiedPrograms->push($program);
            }
        }

        // Apply status filter if provided
        if (! empty($statuses)) {
            $qualifiedPrograms = $qualifiedPrograms->filter(function ($program) use ($statuses) {
                return in_array($program->user_program_status, $statuses);
            });
        }

        return $qualifiedPrograms;
    }

    /**
     * Get programs the user has interacted with.
     */
    public function getUserPrograms(int $userId, array $methodologyIds = [], array $moduleIds = [], array $statuses = []): Collection
    {
        // Build the base query using Eloquent relationships
        $userProgramsQuery = UserProgram::with([
            'program' => function ($query) {
                $query->where('active', true)
                    ->withCount('stepsList as steps_count');
            },
            'program.modules' => function ($query) use ($methodologyIds, $moduleIds) {
                // Apply filters at the relationship level
                if (! empty($methodologyIds)) {
                    $query->whereIn('program_module.methodology_id', $methodologyIds);
                }
                if (! empty($moduleIds)) {
                    $query->whereIn('program_module.module_id', $moduleIds);
                }
            }
        ])->where('user_id', $userId);

        // Note: Status filtering is applied after step-based calculation below

        // Only get programs that are active
        $userProgramsQuery->whereHas('program', function ($query) {
            $query->where('active', true);
        });

        $userPrograms = $userProgramsQuery->get();

        // If we have user programs, get methodologies and pillars data in bulk
        $methodologyIds_toLoad = [];
        $pillarIds_toLoad = [];

        foreach ($userPrograms as $userProgram) {
            if ($userProgram->program) {
                foreach ($userProgram->program->modules as $module) {
                    $methodologyIds_toLoad[] = $module->pivot->methodology_id;
                    if ($module->pivot->pillar_id) {
                        $pillarIds_toLoad[] = $module->pivot->pillar_id;
                    }
                }
            }
        }

        // Load methodologies and pillars in bulk to avoid N+1 queries
        $methodologies = collect();
        $pillars = collect();

        if (! empty($methodologyIds_toLoad)) {
            $methodologies = \App\Models\Methodology::whereIn('id', array_unique($methodologyIds_toLoad))
                ->get()
                ->keyBy('id');
        }

        if (! empty($pillarIds_toLoad)) {
            $pillars = \App\Models\Pillar::whereIn('id', array_unique($pillarIds_toLoad))
                ->get()
                ->keyBy('id');
        }

        // Get user step progress data in bulk if status filtering is needed
        $userStepProgressData = collect();
        if (! empty($statuses) && $userPrograms->isNotEmpty()) {
            $userStepProgressData = \App\Models\UserStepProgress::where('user_id', $userId)
                ->whereIn('program_id', $userPrograms->pluck('program.id')->filter())
                ->get()
                ->groupBy('program_id');
        }

        $result = collect();

        foreach ($userPrograms as $userProgram) {
            $program = $userProgram->program;

            // Skip if program is not active (double-check)
            if (! $program || ! $program->active) {
                continue;
            }

            $qualifyingModule = null;

            // Find the first qualifying module
            foreach ($program->modules as $module) {
                $pivot = $module->pivot;
                $moduleResult = null;

                if ($pivot->pillar_id) {
                    $moduleResult = $this->resultCalculationService->calculateModuleResult(
                        $userId,
                        $module->id,
                        $pivot->methodology_id,
                        $pivot->pillar_id
                    );
                } else {
                    $moduleResult = $this->resultCalculationService->calculateModuleResult(
                        $userId,
                        $module->id,
                        $pivot->methodology_id,
                        null
                    );
                }

                if ($moduleResult && isset($moduleResult['percentage'])) {
                    $userScore = $moduleResult['percentage'];

                    if ($userScore >= $pivot->min_score && $userScore <= $pivot->max_score) {
                        // Get methodology and pillar from loaded collections
                        $methodology = $methodologies->get($pivot->methodology_id);
                        $pillar = $pivot->pillar_id ? $pillars->get($pivot->pillar_id) : null;

                        $qualifyingModule = [
                            'id' => $module->id,
                            'name' => $module->name,
                            'description' => $module->description,
                            'definition' => $module->definition,
                            'objectives' => $module->objectives,
                            'pillar' => $pillar ? [
                                'id' => $pillar->id,
                                'name' => $pillar->name,
                            ] : null,
                            'methodology' => [
                                'id' => $pivot->methodology_id,
                                'name' => $methodology?->name,
                            ],
                            'user_score' => $userScore,
                            'required_range' => [
                                'min' => $pivot->min_score,
                                'max' => $pivot->max_score,
                            ],
                        ];
                        break; // Use first qualifying module
                    }
                }
            }

            // Only include if there's a qualifying module (or no filters applied)
            if ($qualifyingModule || (empty($methodologyIds) && empty($moduleIds))) {
                // Calculate status based on step progress if status filtering is needed
                if (! empty($statuses)) {
                    $stepProgress = $userStepProgressData->get($program->id, collect());
                    $completedSteps = $stepProgress->where('status', 'completed')->count();
                    $inProgressSteps = $stepProgress->where('status', 'in_progress')->count();

                    $programStatus = 'not_started';
                    if ($userProgram->status === 'completed') {
                        $programStatus = 'completed';
                    } elseif ($completedSteps > 0 || $inProgressSteps > 0) {
                        $programStatus = 'in_progress';
                    }

                    $program->user_program_status = $programStatus;
                } else {
                    $program->user_program_status = $userProgram->status;
                }

                $program->started_at = $userProgram->started_at;
                $program->completed_at = $userProgram->completed_at;
                $program->qualifying_module = $qualifyingModule;

                $result->push($program);
            }
        }

        // Apply status filter if provided (after step-based calculation)
        if (! empty($statuses)) {
            $result = $result->filter(function ($program) use ($statuses) {
                return in_array($program->user_program_status, $statuses);
            });
        }

        return $result;
    }

    /**
     * Get unique methodologies and modules from suggested programs for filtering.
     */
    public function getSuggestedProgramsFilters(int $userId): array
    {
        // Reuse the getSuggestedPrograms method to get actual suggested programs
        $suggestedPrograms = $this->getSuggestedPrograms($userId);

        // Extract filters from suggested programs
        $filters = $this->extractFiltersFromPrograms($suggestedPrograms);

        return [
            'methodologies' => $filters['methodologies'],
            'modules' => $filters['modules'],
            'statuses' => ['not_started', 'in_progress', 'completed'],
        ];
    }

    /**
     * Get unique methodologies and modules from user programs for filtering.
     */
    public function getUserProgramsFilters(int $userId): array
    {
        // Reuse the getUserPrograms method to get actual user programs
        $userPrograms = $this->getUserPrograms($userId);

        // Extract filters from user programs
        $filters = $this->extractFiltersFromPrograms($userPrograms);

        return [
            'methodologies' => $filters['methodologies'],
            'modules' => $filters['modules'],
            'statuses' => ['not_started', 'in_progress', 'completed'],
        ];
    }

    /**
     * Extract unique methodologies and modules from programs with qualifying modules.
     */
    private function extractFiltersFromPrograms(Collection $programs): array
    {
        $methodologies = collect();
        $modules = collect();

        foreach ($programs as $program) {
            if (isset($program->qualifying_module)) {
                $qualifyingModule = $program->qualifying_module;

                // Add methodology if not already added
                if (! $methodologies->contains('id', $qualifyingModule['methodology']['id'])) {
                    $methodologies->push([
                        'id' => $qualifyingModule['methodology']['id'],
                        'name' => $qualifyingModule['methodology']['name'],
                    ]);
                }

                // Add module if not already added
                if (! $modules->contains('id', $qualifyingModule['id'])) {
                    $modules->push([
                        'id' => $qualifyingModule['id'],
                        'name' => $qualifyingModule['name'],
                    ]);
                }
            }
        }

        return [
            'methodologies' => $methodologies->sortBy('name')->values()->toArray(),
            'modules' => $modules->sortBy('name')->values()->toArray(),
        ];
    }

    public function startProgram(int $userId, int $programId): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        $existingUserProgram = DB::table('user_programs')
            ->where('user_id', $userId)
            ->where('program_id', $programId)
            ->first();

        if ($existingUserProgram) {
            return false;
        }

        DB::table('user_programs')->insert([
            'user_id' => $userId,
            'program_id' => $programId,
            'status' => 'in_progress',
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    public function completeProgram(int $userId, int $programId): array
    {
        try {
            $userProgram = DB::table('user_programs')
                ->where('user_id', $userId)
                ->where('program_id', $programId)
                ->first();

            if (! $userProgram) {
                return ['success' => false, 'error' => 'program_not_found'];
            }

            if ($userProgram->status === 'completed') {
                return ['success' => false, 'error' => 'program_already_completed'];
            }

            DB::table('user_programs')
                ->where('user_id', $userId)
                ->where('program_id', $programId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            return ['success' => true];
        } catch (\Exception) {
            return ['success' => false, 'error' => 'exception'];
        }
    }

    public function resetProgram(int $userId, int $programId): bool
    {
        try {
            DB::beginTransaction();

            // Check if user has enrolled in this program
            $userProgram = UserProgram::where('user_id', $userId)
                ->where('program_id', $programId)
                ->first();

            if (! $userProgram) {
                DB::rollback();
                return false;
            }

            // Remove all step progress for this user and program
            UserStepProgress::forUserAndProgram($userId, $programId)->delete();

            // Remove user program enrollment
            $userProgram->delete();

            DB::commit();
            return true;
        } catch (\Exception) {
            DB::rollback();
            return false;
        }
    }
}
