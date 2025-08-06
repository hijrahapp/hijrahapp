<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'context_type',
        'context_id',
        'question_id',
        'answer_id',
    ];

    /* -------------------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------------*/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function context(): MorphTo
    {
        return $this->morphTo();
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }
}