<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:50', 'min:1'],
            'avatar' => ['sometimes', 'nullable', 'string', 'url', 'max:500'],
            'kids_mode' => ['sometimes', 'boolean'],
            'parental_controls' => ['sometimes', 'nullable', 'array'],
            'parental_controls.content_rating' => ['in:G,PG,PG-13,R,NC-17'],
            'parental_controls.watch_time_limit' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'parental_controls.require_pin' => ['boolean'],
            'parental_controls.pin_code' => ['nullable', 'string', 'regex:/^\d{4}$/'],
            'preferences' => ['sometimes', 'nullable', 'array'],
            'preferences.language' => ['in:en,es,fr,de,pt,ja,zh,ar,hi'],
            'preferences.subtitle_language' => ['in:en,es,fr,de,pt,ja,zh,ar,hi,none'],
            'preferences.quality' => ['in:auto,480p,720p,1080p,4k'],
            'preferences.autoplay' => ['boolean'],
            'preferences.notifications' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Profile name cannot exceed 50 characters',
            'avatar.url' => 'Avatar must be a valid URL',
            'parental_controls.content_rating.in' => 'Invalid content rating',
            'parental_controls.watch_time_limit.max' => 'Watch time limit cannot exceed 1440 minutes (24 hours)',
            'parental_controls.pin_code.regex' => 'PIN code must be exactly 4 digits',
        ];
    }
}
