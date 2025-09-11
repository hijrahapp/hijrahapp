<?php

namespace App\Http\Repositories;

use App\Models\Program;
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

    /**
     * Get programs suggested for user based on their completed modules and scores.
     */
    public function getSuggestedPrograms(int $userId, array $methodologyIds = [], array $moduleIds = [], array $statuses = []): Collection
    {
        // Get all program-module combinations
        $programModulesQuery = DB::table('program_module as pm')
            ->join('programs as p', 'p.id', '=', 'pm.program_id')
            ->join('modules as m', 'm.id', '=', 'pm.module_id')
            ->join('methodology as mth', 'mth.id', '=', 'pm.methodology_id')
            ->leftJoin('pillars as pil', 'pil.id', '=', 'pm.pillar_id')
            ->leftJoin('user_programs as up', function ($join) use ($userId) {
                $join->on('up.program_id', '=', 'pm.program_id')
                    ->where('up.user_id', '=', $userId);
            })
            // ->whereNull('up.id') // Exclude programs user is already enrolled in
            ->select([
                'pm.program_id',
                'pm.module_id',
                'pm.methodology_id',
                'pm.pillar_id',
                'pm.min_score',
                'pm.max_score',
                'p.name as program_name',
                'p.description as program_description',
                'p.definition as program_definition',
                'p.objectives as program_objectives',
                'p.created_at as program_created_at',
                'p.updated_at as program_updated_at',
                'm.name as module_name',
                'm.description as module_description',
                'm.definition as module_definition',
                'm.objectives as module_objectives',
                'mth.name as methodology_name',
                'pil.name as pillar_name',
            ]);

        // Apply filters
        if (! empty($methodologyIds)) {
            $programModulesQuery->whereIn('pm.methodology_id', $methodologyIds);
        }
        if (! empty($moduleIds)) {
            $programModulesQuery->whereIn('pm.module_id', $moduleIds);
        }

        $programModules = $programModulesQuery->get();

        $qualifiedPrograms = collect();

        foreach ($programModules as $pm) {
            // Check if module is completed and calculate score
            $moduleResult = null;

            if ($pm->pillar_id) {
                // Module under pillar context
                $moduleResult = $this->resultCalculationService->calculateModuleResult(
                    $userId,
                    $pm->module_id,
                    $pm->methodology_id,
                    $pm->pillar_id
                );
            } else {
                // Module directly under methodology
                $moduleResult = $this->resultCalculationService->calculateModuleResult(
                    $userId,
                    $pm->module_id,
                    $pm->methodology_id,
                    null
                );
            }

            // Check if module is completed and score qualifies
            if ($moduleResult && isset($moduleResult['percentage'])) {
                $userScore = $moduleResult['percentage'];

                if ($userScore >= $pm->min_score && $userScore <= $pm->max_score) {
                    // Check if we already have this program in our qualified list
                    $existingProgram = $qualifiedPrograms->firstWhere('id', $pm->program_id);

                    if (! $existingProgram) {
                        $program = new Program;
                        $program->id = $pm->program_id;
                        $program->name = $pm->program_name;
                        $program->description = $pm->program_description;
                        $program->definition = $pm->program_definition;
                        $program->objectives = $pm->program_objectives;
                        $program->created_at = $pm->program_created_at;
                        $program->updated_at = $pm->program_updated_at;

                        // Add the qualifying module details
                        $program->qualifying_module = [
                            'id' => $pm->module_id,
                            'name' => $pm->module_name,
                            'description' => $pm->module_description,
                            'definition' => $pm->module_definition,
                            'objectives' => $pm->module_objectives,
                            'pillar' => $pm->pillar_id ? [
                                'id' => $pm->pillar_id,
                                'name' => $pm->pillar_name,
                            ] : null,
                            'methodology' => [
                                'id' => $pm->methodology_id,
                                'name' => $pm->methodology_name,
                            ],
                            'user_score' => $userScore,
                            'required_range' => [
                                'min' => $pm->min_score,
                                'max' => $pm->max_score,
                            ],
                        ];

                        $stepsCount = DB::table('steps')
                            ->where('program_id', $pm->program_id)
                            ->count();
                        $program->steps_count = $stepsCount;

                        $qualifiedPrograms->push($program);
                    }
                }
            }
        }

        // Apply status filter if provided
        if (! empty($statuses)) {
            $qualifiedPrograms = $qualifiedPrograms->filter(function ($program) use ($statuses, $userId) {
                // Get the user's status for this program
                $userProgram = DB::table('user_programs')
                    ->where('user_id', $userId)
                    ->where('program_id', $program->id)
                    ->first();
                
                $programStatus = $userProgram ? $userProgram->status : 'not_started';
                
                return in_array($programStatus, $statuses);
            });
        }

        return $qualifiedPrograms;
    }

    /**
     * Get programs the user has interacted with.
     */
    public function getUserPrograms(int $userId, array $methodologyIds = [], array $moduleIds = [], array $statuses = []): Collection
    {
        // Get all user programs
        $userProgramsQuery = DB::table('user_programs as up')
            ->join('programs as p', 'p.id', '=', 'up.program_id')
            ->where('up.user_id', $userId)
            ->select([
                'up.program_id',
                'up.status as user_program_status',
                'up.started_at',
                'up.completed_at',
                'p.name as program_name',
                'p.description as program_description',
                'p.definition as program_definition',
                'p.objectives as program_objectives',
                'p.created_at as program_created_at',
                'p.updated_at as program_updated_at',
            ]);

        // Apply status filter
        if (! empty($statuses)) {
            $userProgramsQuery->whereIn('up.status', $statuses);
        }

        $userPrograms = $userProgramsQuery->get();
        $result = collect();

        foreach ($userPrograms as $up) {
            // Get program-module combinations for this program
            $programModulesQuery = DB::table('program_module as pm')
                ->join('modules as m', 'm.id', '=', 'pm.module_id')
                ->join('methodology as mth', 'mth.id', '=', 'pm.methodology_id')
                ->leftJoin('pillars as pil', 'pil.id', '=', 'pm.pillar_id')
                ->where('pm.program_id', $up->program_id)
                ->select([
                    'pm.module_id',
                    'pm.methodology_id',
                    'pm.pillar_id',
                    'pm.min_score',
                    'pm.max_score',
                    'm.name as module_name',
                    'm.description as module_description',
                    'm.definition as module_definition',
                    'm.objectives as module_objectives',
                    'mth.name as methodology_name',
                    'pil.name as pillar_name',
                ]);

            // Apply methodology and module filters
            if (! empty($methodologyIds)) {
                $programModulesQuery->whereIn('pm.methodology_id', $methodologyIds);
            }
            if (! empty($moduleIds)) {
                $programModulesQuery->whereIn('pm.module_id', $moduleIds);
            }

            $programModules = $programModulesQuery->get();
            $qualifyingModule = null;

            // Find the first qualifying module
            foreach ($programModules as $pm) {
                $moduleResult = null;

                if ($pm->pillar_id) {
                    $moduleResult = $this->resultCalculationService->calculateModuleResult(
                        $userId,
                        $pm->module_id,
                        $pm->methodology_id,
                        $pm->pillar_id
                    );
                } else {
                    $moduleResult = $this->resultCalculationService->calculateModuleResult(
                        $userId,
                        $pm->module_id,
                        $pm->methodology_id,
                        null
                    );
                }

                if ($moduleResult && isset($moduleResult['percentage'])) {
                    $userScore = $moduleResult['percentage'];

                    if ($userScore >= $pm->min_score && $userScore <= $pm->max_score) {
                        $qualifyingModule = [
                            'id' => $pm->module_id,
                            'name' => $pm->module_name,
                            'description' => $pm->module_description,
                            'definition' => $pm->module_definition,
                            'objectives' => $pm->module_objectives,
                            'pillar' => $pm->pillar_id ? [
                                'id' => $pm->pillar_id,
                                'name' => $pm->pillar_name,
                            ] : null,
                            'methodology' => [
                                'id' => $pm->methodology_id,
                                'name' => $pm->methodology_name,
                            ],
                            'user_score' => $userScore,
                            'required_range' => [
                                'min' => $pm->min_score,
                                'max' => $pm->max_score,
                            ],
                        ];
                        break; // Use first qualifying module
                    }
                }
            }

            // Only include if there's a qualifying module (or no filters applied)
            if ($qualifyingModule || (empty($methodologyIds) && empty($moduleIds))) {
                $program = new Program;
                $program->id = $up->program_id;
                $program->name = $up->program_name;
                $program->description = $up->program_description;
                $program->definition = $up->program_definition;
                $program->objectives = $up->program_objectives;
                $program->created_at = $up->program_created_at;
                $program->updated_at = $up->program_updated_at;

                // Add user program specific data
                $program->user_program_status = $up->user_program_status;
                $program->started_at = $up->started_at;
                $program->completed_at = $up->completed_at;
                $program->qualifying_module = $qualifyingModule;

                // Add steps count for this program
                $stepsCount = DB::table('steps')
                    ->where('program_id', $up->program_id)
                    ->count();
                $program->steps_count = $stepsCount;

                $result->push($program);
            }
        }

        return $result;
    }

    /**
     * Get unique methodologies and modules from suggested programs for filtering.
     */
    public function getSuggestedProgramsFilters(int $userId): array
    {
        // Get all program-module combinations for programs user is not enrolled in
        $programModules = DB::table('program_module as pm')
            ->join('methodology as mth', 'mth.id', '=', 'pm.methodology_id')
            ->join('modules as m', 'm.id', '=', 'pm.module_id')
            ->leftJoin('user_programs as up', function ($join) use ($userId) {
                $join->on('up.program_id', '=', 'pm.program_id')
                    ->where('up.user_id', '=', $userId);
            })
            // ->whereNull('up.id') // Exclude programs user is already enrolled in
            ->select([
                'pm.methodology_id',
                'mth.name as methodology_name',
                'pm.module_id',
                'm.name as module_name',
            ])
            ->distinct()
            ->get();

        $methodologies = $programModules->groupBy('methodology_id')->map(function ($group) {
            $first = $group->first();

            return [
                'id' => $first->methodology_id,
                'name' => $first->methodology_name,
            ];
        })->values();

        $modules = $programModules->groupBy('module_id')->map(function ($group) {
            $first = $group->first();

            return [
                'id' => $first->module_id,
                'name' => $first->module_name,
            ];
        })->values();

        return [
            'methodologies' => $methodologies->toArray(),
            'modules' => $modules->toArray(),
            'statuses' => ['not_started', "in_progress", "completed"] // Suggested programs are always not started
        ];
    }

    /**
     * Get unique methodologies and modules from user programs for filtering.
     */
    public function getUserProgramsFilters(int $userId): array
    {
        // Get all program-module combinations for user's programs
        $programModules = DB::table('user_programs as up')
            ->join('program_module as pm', 'pm.program_id', '=', 'up.program_id')
            ->join('methodology as mth', 'mth.id', '=', 'pm.methodology_id')
            ->join('modules as m', 'm.id', '=', 'pm.module_id')
            ->where('up.user_id', $userId)
            ->select([
                'pm.methodology_id',
                'mth.name as methodology_name',
                'pm.module_id',
                'm.name as module_name',
            ])
            ->distinct()
            ->get();

        $methodologies = $programModules->groupBy('methodology_id')->map(function ($group) {
            $first = $group->first();

            return [
                'id' => $first->methodology_id,
                'name' => $first->methodology_name,
            ];
        })->values();

        $modules = $programModules->groupBy('module_id')->map(function ($group) {
            $first = $group->first();

            return [
                'id' => $first->module_id,
                'name' => $first->module_name,
            ];
        })->values();

        return [
            'methodologies' => $methodologies->toArray(),
            'modules' => $modules->toArray(),
            'statuses' => ['not_started', 'in_progress', 'completed'],
        ];
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
}
