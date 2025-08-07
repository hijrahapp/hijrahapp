<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Methodology extends Model
{
    use HasFactory;

    protected $table = 'methodology';

    protected $fillable = [
        'name',
        'description',
        'definition',
        'objectives',
        'type',
        'first_section_name',
        'second_section_name',
        'pillars_definition',
        'modules_definition',
        'questions_description',
        'questions_estimated_time',
        'questions_count',
        'first_section_description',
        'second_section_description',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
     * Pillars that belong to the methodology.
     */
    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'methodology_pillar')->withPivot('section');
    }

    /**
     * Modules that belong directly to the methodology (when there is no pillar level).
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'methodology_module');
    }

    /**
     * Questions attached directly to the methodology (outside pillars/modules).
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'methodology_question')->withPivot('weight');
    }

    /**
     * Pillar questions within this methodology.
     */
    public function pillarQuestions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'pillar_question')
            ->withPivot('pillar_id', 'weight')
            ->using(\App\Models\PillarQuestion::class);
    }

    /**
     * Module questions within this methodology.
     */
    public function moduleQuestions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'module_question')
            ->withPivot('module_id', 'pillar_id', 'weight')
            ->using(\App\Models\ModuleQuestion::class);
    }

    /**
     * Modules within pillars of this methodology.
     */
    public function pillarModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'pillar_module')
            ->withPivot('pillar_id')
            ->wherePivot('methodology_id', $this->id);
    }

    /**
     * Modules within a specific pillar of this methodology.
     */
    public function modulesInPillar(int $pillarId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'pillar_module')
            ->withPivot('pillar_id')
            ->wherePivot('methodology_id', $this->id)
            ->wherePivot('pillar_id', $pillarId);
    }


}