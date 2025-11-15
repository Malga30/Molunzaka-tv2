<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get all profiles for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $profiles = $user->profiles()->orderBy('created_at', 'asc')->get();
        $currentProfileId = session("user_profile_{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Profiles retrieved successfully',
            'data' => [
                'profiles' => $profiles,
                'total' => $profiles->count(),
                'limit' => 5,
                'remaining' => $user->remainingProfiles(),
                'current_profile_id' => $currentProfileId,
            ],
        ]);
    }

    /**
     * Create a new profile for the authenticated user.
     * Maximum of 5 profiles per user.
     */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has reached maximum profiles (5)
        if ($user->hasMaxProfiles()) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum profile limit (5) reached',
                'error' => 'profile_limit_exceeded',
                'data' => [
                    'current_count' => $user->profiles()->count(),
                    'max_allowed' => 5,
                    'remaining' => 0,
                ],
            ], 422);
        }

        // Create the profile
        $profile = $user->profiles()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'data' => $profile,
        ], 201);
    }

    /**
     * Get a specific profile.
     */
    public function show(Request $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Update an existing profile.
     */
    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        $profile->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $profile,
        ]);
    }

    /**
     * Delete a profile.
     */
    public function destroy(Request $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        // Prevent deletion if it's the only profile
        $profileCount = $request->user()->profiles()->count();
        if ($profileCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'You must have at least one profile',
                'error' => 'cannot_delete_only_profile',
            ], 400);
        }

        // Clear session if this was the current profile
        if (session("user_profile_{$profile->user_id}") === $profile->id) {
            session()->forget("user_profile_{$profile->user_id}");
        }

        $profileName = $profile->name;
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => "Profile '{$profileName}' deleted successfully",
            'data' => [
                'deleted_id' => $profile->id,
                'deleted_name' => $profileName,
            ],
        ]);
    }

    /**
     * Switch to a specific profile (update session state).
     * This stores the active profile in the user's session.
     */
    public function switch(Request $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        // Store active profile in session
        $request->user()->setCurrentProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Switched to profile '{$profile->name}' successfully",
            'data' => [
                'profile_id' => $profile->id,
                'profile_name' => $profile->name,
                'kids_mode' => $profile->kids_mode,
                'active' => true,
            ],
        ]);
    }

    /**
     * Get current active profile.
     */
    public function current(Request $request): JsonResponse
    {
        $currentProfile = $request->user()->getCurrentProfile();

        if (!$currentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'No active profile found',
                'error' => 'no_profile_found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Current profile retrieved successfully',
            'data' => $currentProfile,
        ]);
    }

    /**
     * Update parental controls for a profile.
     */
    public function updateParentalControls(Request $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        $request->validate([
            'content_rating' => ['in:G,PG,PG-13,R,NC-17'],
            'watch_time_limit' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'require_pin' => ['boolean'],
            'pin_code' => ['nullable', 'string', 'regex:/^\d{4}$/'],
        ]);

        $profile->updateParentalControls($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Parental controls updated successfully',
            'data' => [
                'profile_id' => $profile->id,
                'parental_controls' => $profile->getParentalControls(),
            ],
        ]);
    }

    /**
     * Update preferences for a profile.
     */
    public function updatePreferences(Request $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not own this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        $request->validate([
            'language' => ['in:en,es,fr,de,pt,ja,zh,ar,hi'],
            'subtitle_language' => ['in:en,es,fr,de,pt,ja,zh,ar,hi,none'],
            'quality' => ['in:auto,480p,720p,1080p,4k'],
            'autoplay' => ['boolean'],
            'notifications' => ['boolean'],
        ]);

        $profile->updatePreferences($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => [
                'profile_id' => $profile->id,
                'preferences' => $profile->getPreferences(),
            ],
        ]);
    }
}
