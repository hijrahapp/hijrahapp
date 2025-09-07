<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Objective extends Model
{
    /** @use HasFactory<\Database\Factories\ObjectiveFactory> */
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'type',
        'time_to_finish',
        'time_type',
        'type_specific_data',
        'order',
    ];

    protected $casts = [
        'type_specific_data' => 'array',
        'time_to_finish' => 'integer',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Available objective types
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
        'hours' => 'Hours',
        'days' => 'Days',
        'weeks' => 'Weeks',
        'months' => 'Months',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Program that this objective belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /* -------------------------------------------------------------------------
     | Accessors & Mutators
     |------------------------------------------------------------------------*/

    /**
     * Get the type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get the time type display name
     */
    public function getTimeTypeDisplayAttribute(): string
    {
        return self::TIME_TYPES[$this->time_type] ?? $this->time_type;
    }

    /**
     * Get formatted time duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $unit = $this->time_to_finish === 1 
            ? rtrim($this->time_type_display, 's') 
            : $this->time_type_display;
        
        return "{$this->time_to_finish} {$unit}";
    }

    /* -------------------------------------------------------------------------
     | Helper Methods
     |------------------------------------------------------------------------*/

    /**
     * Get type-specific data with defaults based on objective type
     */
    public function getTypeData(): array
    {
        $data = $this->type_specific_data ?? [];
        
        return match ($this->type) {
            'journal' => array_merge(['header' => ''], $data),
            'article' => array_merge(['content' => ''], $data),
            'advice' => array_merge(['header' => '', 'advices' => []], $data),
            'daily_mission' => array_merge(['header' => '', 'content' => ''], $data),
            'quiz' => array_merge(['questions' => []], $data),
            'video' => array_merge(['content_url' => '', 'description' => ''], $data),
            'audio' => array_merge(['content_url' => '', 'description' => ''], $data),
            'book' => array_merge(['content_url' => '', 'cover_image' => '', 'description' => ''], $data),
            'challenge' => array_merge(['description' => '', 'challenges' => []], $data),
            default => $data,
        };
    }

    /**
     * Set type-specific data with validation
     */
    public function setTypeData(array $data): void
    {
        $this->type_specific_data = $this->validateTypeData($data);
    }

    /**
     * Validate type-specific data based on objective type
     */
    protected function validateTypeData(array $data): array
    {
        return match ($this->type) {
            'journal' => [
                'header' => $data['header'] ?? '',
            ],
            'article' => [
                'content' => $data['content'] ?? '',
            ],
            'advice' => [
                'header' => $data['header'] ?? '',
                'advices' => array_values($data['advices'] ?? []),
            ],
            'daily_mission' => [
                'header' => $data['header'] ?? '',
                'content' => $data['content'] ?? '',
            ],
            'quiz' => [
                'questions' => array_values($data['questions'] ?? []),
            ],
            'video' => [
                'content_url' => $data['content_url'] ?? '',
                'description' => $data['description'] ?? '',
            ],
            'audio' => [
                'content_url' => $data['content_url'] ?? '',
                'description' => $data['description'] ?? '',
            ],
            'book' => [
                'content_url' => $data['content_url'] ?? '',
                'cover_image' => $data['cover_image'] ?? '',
                'description' => $data['description'] ?? '',
            ],
            'challenge' => [
                'description' => $data['description'] ?? '',
                'challenges' => array_values($data['challenges'] ?? []),
            ],
            default => $data,
        };
    }

    /* -------------------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------------*/

    /**
     * Scope to get objectives ordered by their order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope to get objectives by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
