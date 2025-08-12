<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'tags',
        'active',
    ];

    protected $casts = [
        'type' => QuestionType::class,
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    public function answers(): BelongsToMany
    {
        return $this->belongsToMany(Answer::class, 'questions_answers');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_question')
            ->withPivot('methodology_id', 'pillar_id', 'weight');
    }

    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'pillar_question')
            ->withPivot('methodology_id', 'weight');
    }

    public function methodologies(): BelongsToMany
    {
        return $this->belongsToMany(Methodology::class, 'methodology_question')->withPivot('weight');
    }


    /**
     * Context-specific weights for answers linked through this question.
     */
    public function answerWeights(): HasMany
    {
        return $this->hasMany(QuestionAnswerWeight::class);
    }

    /* -------------------------------------------------------------------------
     | Accessors & Mutators
     |------------------------------------------------------------------------*/
    public function getTagsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = json_encode($value);
    }
}