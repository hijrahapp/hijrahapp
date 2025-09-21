<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEnrichment extends Model
{
    protected $fillable = [
        'user_id',
        'enrichment_id',
        'like',
        'favorite',
    ];

    protected $casts = [
        'like' => 'boolean',
        'favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrichment(): BelongsTo
    {
        return $this->belongsTo(Enrichment::class);
    }
}
