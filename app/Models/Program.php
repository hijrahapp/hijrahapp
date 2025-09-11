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
        'img_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Steps linked to this program.
     */
    public function stepsList(): HasMany
    {
        return $this->hasMany(Step::class)->orderBy('id', 'asc');
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
    public function modulesForMethodology(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps();
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

    /**
     * Users who have interacted with this program.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_programs')
            ->withPivot('status', 'started_at', 'completed_at')
            ->withTimestamps();
    }

    /**
     * Users currently working on this program.
     */
    public function usersInProgress(): BelongsToMany
    {
        return $this->users()->wherePivot('status', 'in_progress');
    }

    /**
     * Users who have completed this program.
     */
    public function usersCompleted(): BelongsToMany
    {
        return $this->users()->wherePivot('status', 'completed');
    }

    /**
     * Feedback submissions for this program.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ProgramFeedback::class);
    }

    /**
     * Get average rating for this program.
     */
    public function getAverageRating(): ?float
    {
        return $this->feedback()
            ->selectRaw('AVG(JSON_EXTRACT(responses, "$.overall_rating")) as avg_rating')
            ->value('avg_rating');
    }

    /**
     * Get total feedback count for this program.
     */
    public function getFeedbackCount(): int
    {
        return $this->feedback()->count();
    }
}
