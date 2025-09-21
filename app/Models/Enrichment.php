<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrichment extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'type',
        'img_url',
        'header',
        'content',
        'content_url',
        'content_image',
        'interests',
        'tags',
    ];

    protected $casts = [
        'interests' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the interests associated with this enrichment.
     */
    public function getInterestsObjects()
    {
        if (empty($this->interests)) {
            return collect();
        }

        return Interest::whereIn('id', $this->interests)->get();
    }

    /**
     * Get the tags associated with this enrichment.
     */
    public function getTagsObjects()
    {
        if (empty($this->tags)) {
            return collect();
        }

        return Tag::whereIn('id', $this->tags)->get();
    }
}
