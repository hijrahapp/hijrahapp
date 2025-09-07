<?php

namespace App\Http\Repositories;

use App\Models\Objective;
use App\Models\Program;
use Illuminate\Database\Eloquent\Collection;

class ObjectiveRepository
{
    public function getAll(): Collection
    {
        return Objective::with('program')->ordered()->get();
    }

    public function findById(int $objectiveId): ?Objective
    {
        return Objective::with('program')->find($objectiveId);
    }

    public function findByProgram(int $programId): Collection
    {
        return Objective::where('program_id', $programId)
            ->ordered()
            ->get();
    }


    public function findByProgramAndType(int $programId, string $type): Collection
    {
        return Objective::where('program_id', $programId)
            ->ofType($type)
            ->ordered()
            ->get();
    }

    public function create(array $data): Objective
    {
        // Set order if not provided
        if (!isset($data['order'])) {
            $maxOrder = Objective::where('program_id', $data['program_id'])->max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
        }

        return Objective::create($data);
    }

    public function update(int $objectiveId, array $data): bool
    {
        $objective = Objective::find($objectiveId);
        if (!$objective) {
            return false;
        }

        return $objective->update($data);
    }

    public function delete(int $objectiveId): bool
    {
        $objective = Objective::find($objectiveId);
        if (!$objective) {
            return false;
        }

        return $objective->delete();
    }

    public function reorderObjectives(int $programId, array $objectiveOrders): bool
    {
        try {
            foreach ($objectiveOrders as $order => $objectiveId) {
                Objective::where('id', $objectiveId)
                    ->where('program_id', $programId)
                    ->update(['order' => $order + 1]); // 1-based ordering
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function getProgramWithObjectives(int $programId): ?Program
    {
        return Program::with(['objectives' => function ($query) {
            $query->ordered();
        }])->find($programId);
    }

    public function duplicateObjective(int $objectiveId, ?int $newProgramId = null): ?Objective
    {
        $original = Objective::find($objectiveId);
        if (!$original) {
            return null;
        }

        $targetProgramId = $newProgramId ?? $original->program_id;
        
        // Get next order for target program
        $maxOrder = Objective::where('program_id', $targetProgramId)->max('order') ?? 0;

        $duplicated = $original->replicate([
            'created_at',
            'updated_at'
        ]);
        
        $duplicated->program_id = $targetProgramId;
        $duplicated->order = $maxOrder + 1;
        $duplicated->name = $original->name . ' (Copy)';
        $duplicated->save();

        return $duplicated;
    }

    public function getObjectivesByType(string $type): Collection
    {
        return Objective::with('program')
            ->ofType($type)
            ->ordered()
            ->get();
    }

    public function getStatistics(): array
    {
        $total = Objective::count();
        $byType = Objective::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total' => $total,
            'by_type' => $byType,
        ];
    }
}
