<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $profileId = $request->route('profile');
        
        if (!$profileId) {
            return response()->json([
                'message' => 'Profile ID is required',
                'error' => 'invalid_request',
            ], 400);
        }

        $profile = \App\Models\Profile::find($profileId);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
                'error' => 'profile_not_found',
            ], 404);
        }

        // Verify the authenticated user owns this profile
        if ($profile->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to access this profile',
                'error' => 'unauthorized',
            ], 403);
        }

        // Attach profile to request for use in controller
        $request->merge(['profile' => $profile]);

        return $next($request);
    }
}
