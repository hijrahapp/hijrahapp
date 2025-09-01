<?php

namespace App\Models;

use App\Traits\DeletesStoredImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use DeletesStoredImages, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
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

    protected function imageUrlAttributes(): array
    {
        return ['profile_picture'];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * User's submitted answers.
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }
}
