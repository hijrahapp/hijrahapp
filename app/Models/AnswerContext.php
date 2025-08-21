<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnswerContext extends Model
{
    use HasFactory;

    protected $table = 'answer_contexts';

    protected $fillable = [
        'context_type',
        'context_id',
        'answer_id',
        'weight',
        'dependent_context_type',
        'dependent_context_id',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/
    public function context(): MorphTo
    {
        return $this->morphTo();
    }



    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }
}
