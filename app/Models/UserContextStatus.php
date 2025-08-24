<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContextStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'context_type',
        'context_id',
        'methodology_id',
        'pillar_id',
        'status',
    ];
}


