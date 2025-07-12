<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'roles';

    protected $fillable = ['id', 'name'];

    public function users()
    {
        return $this->hasMany(User::class, 'roleId');
    }

    protected $casts = [
        'name' => RoleName::class,
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

}
