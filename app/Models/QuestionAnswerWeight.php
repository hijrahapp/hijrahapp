<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QuestionAnswerWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'context_type',
        'context_id',
        'answer_id',
        'weight',
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