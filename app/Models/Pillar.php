<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\DeletesStoredImages;

class Pillar extends Model
{
    use HasFactory, DeletesStoredImages;

    protected $fillable = [
        'name',
        'description',
        'definition',
        'objectives',
        'img_url',
        // questions meta moved to methodology_pillar pivot
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
     * Methodologies that use this pillar.
     */
    public function methodologies(): BelongsToMany
    {
        return $this->belongsToMany(Methodology::class, 'methodology_pillar')->withPivot('section');
    }

    /**
     * Modules attached to this pillar.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'pillar_module');
    }

    /**
     * Modules attached to this pillar within a specific methodology.
     */
    public function modulesForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'pillar_module')
            ->withPivot('methodology_id')
            ->wherePivot('methodology_id', $methodologyId);
    }

    /**
     * Questions attached directly to this pillar within a methodology.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'pillar_question')
            ->withPivot('methodology_id', 'weight');
    }

    /**
     * Questions for this pillar within a specific methodology.
     */
    public function questionsForMethodology(int $methodologyId): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'pillar_question')
            ->withPivot('methodology_id', 'weight')
            ->wherePivot('methodology_id', $methodologyId);
    }

    /**
     * Pillars that this pillar depends on (within a certain methodology).
     */
    public function dependsOn(): BelongsToMany
    {
        return $this->belongsToMany(
            Pillar::class,
            'pillar_dependencies',
            'pillar_id', // current pillar
            'depends_on_pillar_id' // pillar it depends on
        )->withPivot('methodology_id');
    }

    /**
     * Pillars that depend on this pillar.
     */
    public function dependedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            Pillar::class,
            'pillar_dependencies',
            'depends_on_pillar_id',
            'pillar_id'
        )->withPivot('methodology_id');
    }

}