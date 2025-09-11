<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Liability extends Model
{
    /** @use HasFactory<\Database\Factories\LiabilityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'title',
        'header',
        'todos',
    ];

    protected function casts(): array
    {
        return [
            'todos' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Modules linked to this liability.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'liability_module')
            ->withPivot('methodology_id', 'pillar_id')
            ->withTimestamps();
    }

    /**
     * Modules linked to this liability for a specific methodology.
     */
    public function modulesForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'liability_module')
            ->withPivot('methodology_id', 'pillar_id')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId);
    }

    /**
     * Modules linked to this liability for a specific pillar within a methodology.
     */
    public function modulesForPillarInMethodology(int $methodologyId, int $pillarId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'liability_module')
            ->withPivot('methodology_id', 'pillar_id')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId)
            ->wherePivot('pillar_id', $pillarId);
    }

    /**
     * Direct methodology modules linked to this liability (not under pillars).
     */
    public function directMethodologyModules(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'liability_module')
            ->withPivot('methodology_id', 'pillar_id')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId)
            ->whereNull('liability_module.pillar_id');
    }

    /**
     * User liability progress records.
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserLiabilityProgress::class);
    }

    /**
     * Users who have interacted with this liability.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_liability_progress')
            ->withPivot('completed_todos', 'is_completed')
            ->withTimestamps();
    }

    /**
     * Users who have completed this liability.
     */
    public function usersCompleted(): BelongsToMany
    {
        return $this->users()->wherePivot('is_completed', true);
    }
}
