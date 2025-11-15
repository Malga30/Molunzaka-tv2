<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if the user's email is verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get all profiles for the user.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Check if user has reached maximum profiles (5).
     */
    public function hasMaxProfiles(): bool
    {
        return $this->profiles()->count() >= 5;
    }

    /**
     * Get the number of profiles the user can still create.
     */
    public function remainingProfiles(): int
    {
        return max(0, 5 - $this->profiles()->count());
    }

    /**
     * Get current active profile from session or default.
     */
    public function getCurrentProfile(): ?Profile
    {
        $profileId = session("user_profile_{$this->id}");
        if ($profileId) {
            return $this->profiles()->find($profileId);
        }
        return $this->profiles()->first();
    }

    /**
     * Set the current active profile.
     */
    public function setCurrentProfile(Profile $profile): bool
    {
        if ($profile->user_id !== $this->id) {
            return false;
        }
        session(["user_profile_{$this->id}" => $profile->id]);
        return true;
    }
}
