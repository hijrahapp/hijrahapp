<?php

namespace App\Models;

use App\Traits\DeletesStoredImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'interests',
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
        'interests' => 'array',
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

    /**
     * Programs the user has interacted with.
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'user_programs')
            ->withPivot('status', 'started_at', 'completed_at')
            ->withTimestamps();
    }

    /**
     * User programs relationship (pivot table access).
     */
    public function userPrograms(): HasMany
    {
        return $this->hasMany(UserProgram::class);
    }

    /**
     * Programs the user is currently working on.
     */
    public function programsInProgress(): BelongsToMany
    {
        return $this->programs()->wherePivot('status', 'in_progress');
    }

    /**
     * Programs the user has completed.
     */
    public function completedPrograms(): BelongsToMany
    {
        return $this->programs()->wherePivot('status', 'completed');
    }

    /**
     * User's step progress records.
     */
    public function stepProgress(): HasMany
    {
        return $this->hasMany(UserStepProgress::class);
    }

    /**
     * Get user's progress for a specific program.
     */
    public function programStepProgress(int $programId): HasMany
    {
        return $this->stepProgress()->where('program_id', $programId);
    }

    /**
     * Get user's completed steps for a specific program.
     */
    public function completedStepsForProgram(int $programId): HasMany
    {
        return $this->programStepProgress($programId)->completed();
    }

    /**
     * Get user's in-progress steps for a specific program.
     */
    public function inProgressStepsForProgram(int $programId): HasMany
    {
        return $this->programStepProgress($programId)->inProgress();
    }

    /**
     * User's feedback submissions.
     */
    public function programFeedback(): HasMany
    {
        return $this->hasMany(ProgramFeedback::class);
    }

    /**
     * Check if user has submitted feedback for a program.
     */
    public function hasSubmittedFeedbackFor(int $programId): bool
    {
        return $this->programFeedback()->where('program_id', $programId)->exists();
    }

    /**
     * Get user's feedback for a specific program.
     */
    public function getFeedbackFor(int $programId): ?ProgramFeedback
    {
        return $this->programFeedback()->where('program_id', $programId)->first();
    }

    /**
     * User's liability progress records.
     */
    public function liabilityProgress(): HasMany
    {
        return $this->hasMany(UserLiabilityProgress::class);
    }

    /**
     * Liabilities the user has interacted with.
     */
    public function liabilities(): BelongsToMany
    {
        return $this->belongsToMany(Liability::class, 'user_liability_progress')
            ->withPivot('completed_todos', 'is_completed')
            ->withTimestamps();
    }
}
