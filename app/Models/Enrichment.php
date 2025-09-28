<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrichment extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'img_url',
        'header',
        'content',
        'content_url',
        'content_image',
        'interests',
        'categories',
        'tags',
        'active',
    ];

    protected $casts = [
        'interests' => 'array',
        'categories' => 'array',
        'tags' => 'array',
        'active' => 'boolean',
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

    /**
     * Get the categories associated with this enrichment.
     */
    public function getCategoriesObjects()
    {
        if (empty($this->categories)) {
            return collect();
        }

        return Category::whereIn('id', $this->categories)->get();
    }
}
