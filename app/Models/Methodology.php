<?php

namespace App\Models;

use App\Traits\DeletesStoredImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Methodology extends Model
{
    use DeletesStoredImages, HasFactory;

    protected $table = 'methodology';

    protected $fillable = [
        'name',
        'description',
        'definition',
        'objectives',
        'img_url',
        'type',
        'pillars_definition',

        'modules_definition',
        'questions_description',
        'questions_estimated_time',

        'first_section_name',
        'first_section_description',
        'first_section_definition',
        'first_section_objectives',
        'first_section_img_url',

        'first_section_pillars_definition',

        'second_section_name',
        'second_section_description',
        'second_section_definition',
        'second_section_objectives',
        'second_section_img_url',

        'second_section_pillars_definition',

        'tags',
        'active',
    ];

    protected $casts = [
        'tags' => 'array',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function imageUrlAttributes(): array
    {
        return [
            'img_url',
            'first_section_img_url',
            'second_section_img_url',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/

    /**
     * Pillars that belong to the methodology.
     */
    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'methodology_pillar')->withPivot('section', 'weight');
    }

    /**
     * Modules that belong directly to the methodology (when there is no pillar level).
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'methodology_module')->withPivot('weight');
    }

    /**
     * Questions attached directly to the methodology (outside pillars/modules).
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'methodology_question')->withPivot('weight', 'sequence');
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
