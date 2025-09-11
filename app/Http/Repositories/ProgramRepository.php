<?php

namespace App\Http\Repositories;

use App\Models\Program;
use App\Services\ResultCalculationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProgramRepository
{
    public function __construct(
        private readonly ResultCalculationService $resultCalculationService
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
     * Get suggested programs that the user is eligible for based on their module scores.
     */
    public function getSuggestedPrograms(int $userId, array $methodologyIds = [], array $moduleIds = [], ?string $status = null): Collection
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

        $userModuleScores = $userModuleQuery->get(['context_id as module_id', 'methodology_id', 'pillar_id']);

        if ($userModuleScores->isEmpty()) {
            return Program::query()->whereRaw('1 = 0')->get(); // Return empty Eloquent Collection
        }

        $eligiblePrograms = collect();

        foreach ($userModuleScores as $moduleScore) {
            // Calculate the user's score for this module
            $moduleResult = $this->resultCalculationService->calculateModuleResult(
                $userId,
                $moduleScore->module_id,
                $moduleScore->methodology_id ?: 0,
                $moduleScore->pillar_id ?: null
            );

            if (! $moduleResult || ! isset($moduleResult['percentage'])) {
                continue;
            }

            $userScore = $moduleResult['percentage'];

            // Find programs where this module score qualifies the user
            $programs = Program::query()
                ->join('program_module', 'programs.id', '=', 'program_module.program_id')
                ->where('program_module.module_id', $moduleScore->module_id)
                ->where('program_module.methodology_id', $moduleScore->methodology_id ?: 0)
                ->where('program_module.min_score', '<=', $userScore)
                ->where('program_module.max_score', '>=', $userScore);

            // Filter by pillar if specified
            if ($moduleScore->pillar_id) {
                $programs->where('program_module.pillar_id', $moduleScore->pillar_id);
            } else {
                $programs->whereNull('program_module.pillar_id');
            }

            $qualifyingPrograms = $programs
                ->leftJoin('methodology', 'program_module.methodology_id', '=', 'methodology.id')
                ->leftJoin('modules', 'program_module.module_id', '=', 'modules.id')
                ->leftJoin('pillars', 'program_module.pillar_id', '=', 'pillars.id')
                ->select(
                    'programs.*',
                    'program_module.min_score',
                    'program_module.max_score',
                    'program_module.methodology_id',
                    'program_module.pillar_id',
                    DB::raw($userScore.' as user_score'),
                    DB::raw($moduleScore->module_id.' as qualifying_module_id'),
                    'methodology.name as methodology_name',
                    'modules.name as module_name',
                    'pillars.name as pillar_name'
                )->get();

            $eligiblePrograms = $eligiblePrograms->merge($qualifyingPrograms);
        }

        // Remove duplicates and convert to Eloquent Collection
        $uniquePrograms = $eligiblePrograms
            ->unique('id')
            ->values();

        if ($uniquePrograms->isEmpty()) {
            return Program::query()->whereRaw('1 = 0')->get(); // Return empty Eloquent Collection
        }

        // Convert to Eloquent models while preserving the additional data
        $programs = $uniquePrograms->map(function ($programData) {
            // Create a Program model instance
            $program = new Program;
            $program->fill($programData->toArray());
            $program->exists = true; // Mark as existing to avoid save issues

            // Ensure the ID is properly set
            $program->setAttribute('id', $programData->id);

            // Add the additional fields as attributes
            $program->setAttribute('user_score', $programData->user_score);
            $program->setAttribute('qualifying_module_id', $programData->qualifying_module_id);
            $program->setAttribute('module_name', $programData->module_name);
            $program->setAttribute('methodology_id', $programData->methodology_id);
            $program->setAttribute('methodology_name', $programData->methodology_name);
            $program->setAttribute('pillar_id', $programData->pillar_id);
            $program->setAttribute('pillar_name', $programData->pillar_name);
            $program->setAttribute('min_score', $programData->min_score);
            $program->setAttribute('max_score', $programData->max_score);

            return $program;
        });

        // Apply status filter if provided
        if ($status && $programs->isNotEmpty()) {
            // Get user's program statuses
            $programIds = $programs->pluck('id')->toArray();
            $userPrograms = DB::table('user_programs')
                ->where('user_id', $userId)
                ->whereIn('program_id', $programIds)
                ->pluck('status', 'program_id');

            $programs = $programs->filter(function ($program) use ($status, $userPrograms) {
                $programStatus = $userPrograms->get($program->id, 'not_started');

                return $programStatus === $status;
            });
        }

        // Load the stepsList relationship for each program
        $programIds = $programs->pluck('id');
        $programsWithSteps = Program::query()
            ->whereIn('id', $programIds)
            ->with(['stepsList'])
            ->get()
            ->keyBy('id');

        // Merge the stepsList data into our programs
        $programs->each(function ($program) use ($programsWithSteps) {
            if ($programsWithSteps->has($program->id)) {
                $program->setRelation('stepsList', $programsWithSteps[$program->id]->stepsList);
            }
        });

        return new Collection($programs->sortBy('name')->values());
    }

    /**
     * Get programs the user has interacted with.
     */
    public function getUserPrograms(int $userId, array $methodologyIds = [], array $moduleIds = [], ?string $status = null): Collection
    {
        $query = Program::query()
            ->join('user_programs', 'programs.id', '=', 'user_programs.program_id')
            ->where('user_programs.user_id', $userId);

        // Apply status filter
        if ($status) {
            $query->where('user_programs.status', $status);
        }

        // Apply methodology and module filters by checking program requirements
        if (! empty($methodologyIds) || ! empty($moduleIds)) {
            $query->whereExists(function ($subQuery) use ($methodologyIds, $moduleIds) {
                $subQuery->select(DB::raw(1))
                    ->from('program_module as pm')
                    ->whereRaw('pm.program_id = programs.id');

                if (! empty($methodologyIds)) {
                    $subQuery->whereIn('pm.methodology_id', $methodologyIds);
                }

                if (! empty($moduleIds)) {
                    $subQuery->whereIn('pm.module_id', $moduleIds);
                }
            });
        }

        return $query->select([
            'programs.*',
            'user_programs.status',
            'user_programs.started_at',
            'user_programs.completed_at',
        ])
            ->with(['stepsList'])
            ->orderBy('user_programs.created_at', 'desc')
            ->get();
    }

    /**
     * Start a program for a user.
     */
    public function startProgram(int $userId, int $programId): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        // Check if already started
        $existing = $program->users()
            ->wherePivot('user_id', $userId)
            ->exists();

        if ($existing) {
            return false; // User already has this program
        }

        $program->users()->attach($userId, [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return true;
    }

    /**
     * Complete a program for a user.
     */
    public function completeProgram(int $userId, int $programId): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        $updated = $program->users()
            ->wherePivot('user_id', $userId)
            ->wherePivot('status', 'in_progress')
            ->updateExistingPivot($userId, [
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        return $updated > 0;
    }
}
