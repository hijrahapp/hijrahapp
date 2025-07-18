<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'users';

    protected $fillable = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'otp',
        'otp_expires_at',
        'active',
        'gender',
        'birthDate',
        'roleId',
        'firebase_uid',
        'profile_picture',
    ];

    protected $hidden = [
        'id',
        'password',
        'otp',
        'active',
        'email_verified_at',
        'otp_expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'birthDate' => 'date',
        'active' => 'boolean',
        'password' => 'hashed',
    ];

    public function role() {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array {
        return [];
    }

}
