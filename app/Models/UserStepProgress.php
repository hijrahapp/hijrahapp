<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStepProgress extends Model
{
    /** @use HasFactory<\Database\Factories\UserStepProgressFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'step_id',
        'status',
        'thought',
        'score',
        'challenges_done',
        'percentage',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'challenges_done' => 'json', // Changed from 'array' to 'json' to force reload
        'percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Available status options for user progress
     */
    public const STATUSES = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'skipped' => 'Skipped',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * User who owns this progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Program this progress belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Step this progress is tracking.
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }

    /* -------------------------------------------------------------------------
     | Accessors & Mutators
     |------------------------------------------------------------------------*/

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        if (! $this->status) {
            return 'Not Started';
        }

        $locale = app()->getLocale();
        $translation = trans("lookups.{$this->status}", [], $locale);

        return $translation ?: $this->status;
    }

    /* -------------------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------------*/

    /**
     * Scope to get progress for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get progress for a specific program
     */
    public function scopeForProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope to get progress for a specific step
     */
    public function scopeForStep($query, int $stepId)
    {
        return $query->where('step_id', $stepId);
    }

    /**
     * Scope to get completed progress
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get in progress entries
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get not started entries
     */
    public function scopeNotStarted($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'not_started')
                ->orWhereNull('status');
        });
    }

    /**
     * Scope to get skipped entries
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    /**
     * Scope to get progress for user and program
     */
    public function scopeForUserAndProgram($query, int $userId, int $programId)
    {
        return $query->where('user_id', $userId)->where('program_id', $programId);
    }

    /* -------------------------------------------------------------------------
     | Helper Methods
     |------------------------------------------------------------------------*/

    /**
     * Mark as started
     */
    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as completed with additional data
     */
    public function markAsCompleted(array $additionalData = []): bool
    {
        $data = array_merge([
            'status' => 'completed',
            'completed_at' => now(),
            'percentage' => 100.00,
        ], $additionalData);

        return $this->update($data);
    }

    /**
     * Mark as skipped
     */
    public function markAsSkipped(): bool
    {
        return $this->update([
            'status' => 'skipped',
            'completed_at' => now(),
        ]);
    }

    /**
     * Update progress percentage
     */
    public function updateProgress(float $percentage, array $additionalData = []): bool
    {
        $data = array_merge(['percentage' => $percentage], $additionalData);

        // If percentage is 100, mark as completed
        if ($percentage >= 100) {
            $data['status'] = 'completed';
            $data['completed_at'] = now();
        } elseif ($this->status === 'not_started') {
            $data['status'] = 'in_progress';
            $data['started_at'] = now();
        }

        return $this->update($data);
    }

    /**
     * Check if step is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if step is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if step is not started
     */
    public function isNotStarted(): bool
    {
        return $this->status === 'not_started' || is_null($this->status);
    }

    /**
     * Check if step is skipped
     */
    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }

    /**
     * Get challenges done as array (temporary workaround for caching issue)
     */
    public function getChallengesDoneArray(): array
    {
        $raw = $this->getRawOriginal('challenges_done');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($raw) ? $raw : [];
    }

    /**
     * Get or create progress for user and step
     */
    public static function getOrCreateForUserAndStep(int $userId, int $programId, int $stepId): self
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'program_id' => $programId,
            'step_id' => $stepId,
        ], [
            'status' => 'not_started',
            'percentage' => 0.00,
        ]);
    }
}
