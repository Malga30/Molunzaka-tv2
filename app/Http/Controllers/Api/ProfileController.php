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
        $profiles = $request->user()->profiles()->get();

        return response()->json([
            'message' => 'Profiles retrieved successfully',
            'data' => $profiles,
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
        $profileCount = $user->profiles()->count();
        if ($profileCount >= 5) {
            return response()->json([
                'message' => 'Maximum profile limit (5) reached',
                'error' => 'profile_limit_exceeded',
                'data' => [
                    'current_count' => $profileCount,
                    'max_allowed' => 5,
                ],
            ], 422);
        }

        // Create the profile
        $profile = $user->profiles()->create([
            'name' => $request->input('name'),
            'avatar' => $request->input('avatar'),
            'kids_mode' => $request->input('kids_mode', false),
            'parental_controls' => $request->input('parental_controls', []),
        ]);

        return response()->json([
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
                'message' => 'Unauthorized',
                'error' => 'unauthorized',
            ], 403);
        }

        return response()->json([
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
                'message' => 'Unauthorized',
                'error' => 'unauthorized',
            ], 403);
        }

        $profile->update($request->validated());

        return response()->json([
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
                'message' => 'Unauthorized',
                'error' => 'unauthorized',
            ], 403);
        }

        // Store profile name for response
        $profileName = $profile->name;
        $profile->delete();

        return response()->json([
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
    public function switchProfile(Request $request, Profile $profile): JsonResponse
    {
        // Verify ownership
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'unauthorized',
            ], 403);
        }

        // Store active profile in session
        session(['active_profile_id' => $profile->id]);

        // Also update user's relationship for convenience
        $user = $request->user();
        $user->active_profile_id = $profile->id;
        $user->save();

        return response()->json([
            'message' => "Switched to profile '{$profile->name}'",
            'data' => [
                'profile_id' => $profile->id,
                'profile_name' => $profile->name,
                'kids_mode' => $profile->kids_mode,
                'active' => true,
            ],
        ]);
    }
}
