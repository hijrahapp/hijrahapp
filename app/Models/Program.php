<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    /** @use HasFactory<\Database\Factories\ProgramFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'definition',
        'objectives',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Objectives linked to this program.
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(Objective::class)->ordered();
    }

    /**
     * Modules linked to this program with score ranges.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps();
    }

    /**
     * Modules linked to this program for a specific methodology.
     */
    public function modulesForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId);
    }

    /**
     * Modules linked to this program for a specific pillar within a methodology.
     */
    public function modulesForPillarInMethodology(int $methodologyId, int $pillarId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId)
            ->wherePivot('pillar_id', $pillarId);
    }

    /**
     * Direct methodology modules linked to this program (not under pillars).
     */
    public function directMethodologyModules(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId)
            ->whereNull('program_module.pillar_id');
    }
}
