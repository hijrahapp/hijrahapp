<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Step extends Model
{
    /** @use HasFactory<\Database\Factories\StepFactory> */
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'type',
        'time_to_finish',
        'time_type',
        'type_specific_data',
        'header',
        'content',
        'description',
        'content_url',
        'content_image',
        'advices',
        'challenges',
    ];

    protected $casts = [
        'time_to_finish' => 'integer',
        'type_specific_data' => 'json',
        'advices' => 'json',
        'challenges' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Available step types
     */
    public const TYPES = [
        'journal' => 'Journal',
        'article' => 'Article',
        'advice' => 'Advice',
        'daily_mission' => 'Daily Mission',
        'quiz' => 'Quiz',
        'video' => 'Video',
        'audio' => 'Audio',
        'book' => 'Book',
        'challenge' => 'Challenge',
    ];

    /**
     * Available time types
     */
    public const TIME_TYPES = [
        'minutes' => 'Minutes',
        'hours' => 'Hours',
        'days' => 'Days',
        'weeks' => 'Weeks',
        'months' => 'Months',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Program that this step belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * User progress records for this step.
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserStepProgress::class);
    }

    /**
     * Get progress for a specific user.
     */
    public function progressForUser(int $userId): ?UserStepProgress
    {
        return $this->userProgress()->where('user_id', $userId)->first();
    }

    /* -------------------------------------------------------------------------
     | Accessors & Mutators
     |------------------------------------------------------------------------*/

    /**
     * Get the type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        $locale = app()->getLocale();
        $translation = trans("lookups.{$this->type}", [], $locale);

        return $translation ?: $this->type;
    }

    /**
     * Get the time type display name
     */
    public function getTimeTypeDisplayAttribute(): string
    {
        $locale = app()->getLocale();
        $translation = trans("lookups.{$this->time_type}", [], $locale);

        return $translation ?: $this->time_type;
    }

    /**
     * Get formatted time duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $isPlural = $this->time_to_finish > 1;
        $locale = app()->getLocale();

        // Get the appropriate time type key for lookup
        $timeTypeKey = $isPlural ? $this->time_type : rtrim($this->time_type, 's');

        // Get the translated time type
        $unit = trans("lookups.{$timeTypeKey}", [], $locale);

        return "{$this->time_to_finish} {$unit}";
    }

    /* -------------------------------------------------------------------------
     | Helper Methods
     |------------------------------------------------------------------------*/

    /**
     * Validate step fields based on type
     */
    public function getRequiredFields(): array
    {
        return match ($this->type) {
            'journal' => ['header'],
            'article' => ['header', 'content'],
            'advice' => ['header', 'advices'],
            'daily_mission' => ['header', 'content'],
            'quiz' => [], // Questions will be managed separately
            'video', 'audio' => ['content_url', 'description'],
            'book' => ['content_url', 'content_image', 'description'],
            'challenge' => ['header', 'challenges'],
            default => [],
        };
    }

    /* -------------------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------------*/

    /**
     * Scope to get steps ordered by creation date (newest first)
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get steps by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get steps by program
     */
    public function scopeForProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Get the questions for quiz-type steps
     */
    public function questions()
    {
        return $this->belongsToMany(
            \App\Models\Question::class,
            'step_question',
            'step_id',
            'question_id'
        )->withPivot('correct_answer_id', 'sequence')
            ->withTimestamps()
            ->orderBy('step_question.sequence');
    }

    /**
     * Get or create user progress for this step
     */
    public function getOrCreateProgressForUser(int $userId, int $programId): UserStepProgress
    {
        return UserStepProgress::getOrCreateForUserAndStep($userId, $programId, $this->id);
    }
}
