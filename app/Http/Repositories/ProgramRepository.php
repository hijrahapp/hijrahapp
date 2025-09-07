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

    public function getAll(): Collection
    {
        return Program::all();
    }

    public function findById(int $programId): ?Program
    {
        return Program::find($programId);
    }

    public function create(array $data): Program
    {
        return Program::create($data);
    }

    public function update(int $programId, array $data): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        return $program->update($data);
    }

    public function delete(int $programId): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        return $program->delete();
    }

    /**
     * Get program with all its modules and relationships.
     */
    public function findByIdWithModules(int $programId): ?Program
    {
        return Program::with(['modules', 'objectives'])->find($programId);
    }

    /**
     * Get program with modules for a specific methodology.
     */
    public function findByIdWithModulesForMethodology(int $programId, int $methodologyId): ?Program
    {
        return Program::with(['modulesForMethodology' => function ($query) use ($methodologyId) {
            $query->where('program_module.methodology_id', $methodologyId);
        }])->find($programId);
    }

    /**
     * Attach a module to a program with score configuration.
     */
    public function attachModule(int $programId, array $moduleData): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        // Check if this relationship already exists
        $existingRelation = $program->modules()
            ->wherePivot('module_id', $moduleData['module_id'])
            ->wherePivot('methodology_id', $moduleData['methodology_id'])
            ->wherePivot('pillar_id', $moduleData['pillar_id'] ?? null)
            ->exists();

        if ($existingRelation) {
            return false; // Relationship already exists
        }

        $program->modules()->attach($moduleData['module_id'], [
            'methodology_id' => $moduleData['methodology_id'],
            'pillar_id' => $moduleData['pillar_id'] ?? null,
            'min_score' => $moduleData['min_score'] ?? 0.00,
            'max_score' => $moduleData['max_score'] ?? 100.00,
        ]);

        return true;
    }

    /**
     * Detach a module from a program.
     */
    public function detachModule(int $programId, int $moduleId, int $methodologyId, ?int $pillarId = null): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        $query = $program->modules()
            ->wherePivot('module_id', $moduleId)
            ->wherePivot('methodology_id', $methodologyId);

        if ($pillarId !== null) {
            $query->wherePivot('pillar_id', $pillarId);
        } else {
            $query->whereNull('program_module.pillar_id');
        }

        return $query->detach() > 0;
    }

    /**
     * Update module score configuration for a program.
     */
    public function updateModuleScores(int $programId, int $moduleId, int $methodologyId, ?int $pillarId, array $scoreData): bool
    {
        $program = Program::find($programId);
        if (! $program) {
            return false;
        }

        $program->modules()
            ->wherePivot('module_id', $moduleId)
            ->wherePivot('methodology_id', $methodologyId)
            ->wherePivot('pillar_id', $pillarId)
            ->updateExistingPivot($moduleId, [
                'min_score' => $scoreData['min_score'] ?? 0.00,
                'max_score' => $scoreData['max_score'] ?? 100.00,
            ]);

        return true;
    }

    /**
     * Get all modules available for linking to programs within a methodology.
     */
    public function getAvailableModulesForMethodology(int $methodologyId): Collection
    {
        // Get direct methodology modules
        $directModules = \App\Models\Methodology::find($methodologyId)
            ?->modules()
            ->select('modules.*', 'methodology_module.methodology_id')
            ->selectRaw('NULL as pillar_id')
            ->get();

        // Get pillar modules
        $pillarModules = \DB::table('pillar_module')
            ->join('modules', 'pillar_module.module_id', '=', 'modules.id')
            ->join('pillars', 'pillar_module.pillar_id', '=', 'pillars.id')
            ->where('pillar_module.methodology_id', $methodologyId)
            ->select(
                'modules.*',
                'pillar_module.methodology_id',
                'pillar_module.pillar_id',
                'pillars.name as pillar_name'
            )
            ->get();

        // Combine both collections
        $allModules = collect();
        if ($directModules) {
            $allModules = $allModules->merge($directModules);
        }
        if ($pillarModules) {
            $allModules = $allModules->merge($pillarModules);
        }

        return $allModules;
    }

    /**
     * Get programs that the user is eligible for based on their module scores.
     */
    public function getProgramsForUser(int $userId): Collection
    {
        // Get all user's completed modules with their methodology and pillar contexts
        $userModuleScores = DB::table('user_context_statuses as ucs')
            ->where('ucs.user_id', $userId)
            ->where('ucs.context_type', 'module')
            ->where('ucs.status', 'completed')
            ->get(['context_id as module_id', 'methodology_id', 'pillar_id']);

        if ($userModuleScores->isEmpty()) {
            return collect();
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

        // Remove duplicates and load relationships
        return $eligiblePrograms
            ->unique('id')
            ->values()
            ->loadMissing(['objectives'])
            ->sortBy('name');
    }
}
