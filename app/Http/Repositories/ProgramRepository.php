<?php

namespace App\Http\Repositories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Collection;

class ProgramRepository
{
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
}
