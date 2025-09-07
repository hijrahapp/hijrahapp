<?php

namespace App\Models;

use App\Traits\DeletesStoredImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use DeletesStoredImages, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'definition',
        'objectives',
        'img_url',
        'questions_description',
        'questions_estimated_time',

        'tags',
        'active',
    ];

    protected $casts = [
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function imageUrlAttributes(): array
    {
        return ['img_url'];
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

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Methodologies that use this module directly.
     */
    public function methodologies(): BelongsToMany
    {
        return $this->belongsToMany(Methodology::class, 'methodology_module');
    }

    /**
     * Pillars that include this module.
     */
    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'pillar_module');
    }

    /**
     * Pillars that include this module within a specific methodology.
     */
    public function pillarsForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'pillar_module')
            ->withPivot('methodology_id')
            ->wherePivot('methodology_id', $methodologyId);
    }

    /**
     * Questions that belong to this module within a methodology.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'module_question')
            ->withPivot('methodology_id', 'pillar_id', 'weight', 'sequence')
            ->orderBy('module_question.sequence', 'asc')
            ->orderBy('module_question.id', 'asc');
    }

    /**
     * Questions for this module within a specific methodology.
     */
    public function questionsForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'module_question')
            ->withPivot('methodology_id', 'pillar_id', 'weight', 'sequence')
            ->wherePivot('methodology_id', $methodologyId)
            ->orderBy('module_question.sequence', 'asc')
            ->orderBy('module_question.id', 'asc');
    }

    /**
     * Questions for this module within a specific pillar of a specific methodology.
     */
    public function questionsForPillarInMethodology(int $methodologyId, int $pillarId): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'module_question')
            ->withPivot('methodology_id', 'pillar_id', 'weight', 'sequence')
            ->wherePivot('methodology_id', $methodologyId)
            ->wherePivot('pillar_id', $pillarId)
            ->orderBy('module_question.sequence', 'asc')
            ->orderBy('module_question.id', 'asc');
    }

    /**
     * Programs that include this module with score ranges.
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps();
    }

    /**
     * Programs that include this module for a specific methodology.
     */
    public function programsForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_module')
            ->withPivot('methodology_id', 'pillar_id', 'min_score', 'max_score')
            ->withTimestamps()
            ->wherePivot('methodology_id', $methodologyId);
    }
}
