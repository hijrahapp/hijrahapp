<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'questions_answers');
    }

    /**
     * Context-specific weights for this answer.
     */
    public function answerWeights(): HasMany
    {
        return $this->hasMany(QuestionAnswerWeight::class);
    }
}