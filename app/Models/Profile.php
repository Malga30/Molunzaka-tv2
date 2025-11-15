<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'avatar',
        'kids_mode',
        'parental_controls',
        'preferences',
    ];

    protected $casts = [
        'kids_mode' => 'boolean',
        'parental_controls' => 'array',
        'preferences' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $visible = [
        'id',
        'user_id',
        'name',
        'avatar',
        'kids_mode',
        'parental_controls',
        'preferences',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if profile is in kids mode.
     */
    public function isKidsMode(): bool
    {
        return $this->kids_mode ?? false;
    }

    /**
     * Check if parental control is enabled for a specific setting.
     */
    public function hasParentalControl(string $setting): bool
    {
        $controls = $this->parental_controls ?? [];
        return (bool) ($controls[$setting] ?? false);
    }

    /**
     * Update parental control setting.
     */
    public function setParentalControl(string $setting, bool $value): void
    {
        $controls = $this->parental_controls ?? [];
        $controls[$setting] = $value;
        $this->update(['parental_controls' => $controls]);
    }

    /**
     * Get parental control settings.
     */
    public function getParentalControls(): array
    {
        return $this->parental_controls ?? [
            'content_rating' => 'G',
            'watch_time_limit' => null,
            'require_pin' => false,
            'pin_code' => null,
        ];
    }

    /**
     * Update parental controls.
     */
    public function updateParentalControls(array $controls): bool
    {
        $this->parental_controls = array_merge($this->getParentalControls(), $controls);
        return $this->save();
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(): array
    {
        return $this->preferences ?? [
            'language' => 'en',
            'subtitle_language' => 'en',
            'quality' => 'auto',
            'autoplay' => true,
            'notifications' => true,
        ];
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(array $preferences): bool
    {
        $this->preferences = array_merge($this->getPreferences(), $preferences);
        return $this->save();
    }
}
