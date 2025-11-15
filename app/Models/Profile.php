<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'avatar',
        'kids_mode',
        'parental_controls',
    ];

    protected $casts = [
        'kids_mode' => 'boolean',
        'parental_controls' => 'array',
    ];

    protected $visible = [
        'id',
        'user_id',
        'name',
        'avatar',
        'kids_mode',
        'parental_controls',
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
}
