<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramFeedback extends Model
{
    protected $table = 'program_feedback';

    protected $fillable = [
        'user_id',
        'program_id',
        'responses',
        'form_version',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Get the user who submitted the feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the program this feedback is for.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get a specific response value by key.
     */
    public function getResponse(string $key, mixed $default = null): mixed
    {
        return data_get($this->responses, $key, $default);
    }

    /**
     * Get overall rating from responses.
     */
    public function getOverallRating(): ?int
    {
        return $this->getResponse('overall_rating');
    }

    /**
     * Get life improvement rating from responses.
     */
    public function getLifeImprovementRating(): ?string
    {
        return $this->getResponse('life_improvement');
    }

    /**
     * Get content clarity rating from responses.
     */
    public function getContentClarityRating(): ?string
    {
        return $this->getResponse('content_clarity');
    }

    /**
     * Get most beneficial content types from responses.
     */
    public function getMostBeneficialContent(): ?array
    {
        return $this->getResponse('most_beneficial_content', []);
    }

    /**
     * Get improvement suggestions from responses.
     */
    public function getImprovementSuggestions(): ?string
    {
        return $this->getResponse('improvement_suggestions');
    }
}
