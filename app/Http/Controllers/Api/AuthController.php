<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct(private AuthenticationService $authService)
    {
    }

    /**
     * Register a new user.
     *
     * POST /api/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Create the user
            $user = $this->authService->register($request->validated());

            // Send email verification notification
            $user->notify(new VerifyEmailNotification($user));

            // Create API token
            $tokenResponse = $this->authService->login($user, $request->header('User-Agent') ?? 'web');

            return response()->json([
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $tokenResponse['token'],
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register user.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Login user and issue token.
     *
     * POST /api/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Please verify your email address before logging in.',
                    'data' => [
                        'requires_verification' => true,
                        'user_id' => $user->id,
                    ],
                ], 403);
            }

            // Generate token
            $tokenResponse = $this->authService->login($user, $request->header('User-Agent') ?? 'web');

            return response()->json([
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                    'token' => $tokenResponse['token'],
                    'token_type' => 'Bearer',
                    'remember_me' => $request->boolean('remember_me', false),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Logout user and revoke token.
     *
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Revoke the current token
            $this->authService->logoutToken($request->user()->currentAccessToken()->id);

            return response()->json([
                'message' => 'Logged out successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Logout user from all devices.
     *
     * POST /api/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Revoke all tokens
            $this->authService->logout($user);

            return response()->json([
                'message' => 'Logged out from all devices successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Request password reset.
     *
     * POST /api/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Don't reveal if email exists for security
                return response()->json([
                    'message' => 'If an account exists with this email, you will receive a password reset link.',
                ], 200);
            }

            // Generate password reset token
            $token = Password::createToken($user);

            // Send password reset notification
            $user->notify(new ResetPasswordNotification($token));

            return response()->json([
                'message' => 'Password reset link has been sent to your email.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process password reset request.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Reset password with token.
     *
     * POST /api/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            }

            // Verify the reset token
            $status = Password::verify($user, $request->token, $request->password);

            if ($status !== Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.',
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Revoke all tokens to force re-login
            $this->authService->logout($user);

            return response()->json([
                'message' => 'Password reset successful. Please log in with your new password.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Verify user email.
     *
     * POST /api/email/verify/{id}/{hash}
     */
    public function verifyEmail(int $id, string $hash): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified.',
                    'data' => [
                        'user_id' => $user->id,
                        'verified' => true,
                    ],
                ], 200);
            }

            // Verify the hash
            if (!$this->authService->verifyEmailToken($user, $hash)) {
                return response()->json([
                    'message' => 'Invalid verification link.',
                ], 422);
            }

            // Mark email as verified
            $user->markEmailAsVerified();

            return response()->json([
                'message' => 'Email verified successfully!',
                'data' => [
                    'user_id' => $user->id,
                    'verified' => true,
                    'email' => $user->email,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email verification failed.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Resend verification email.
     *
     * POST /api/email/resend-verification
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Check if email is already verified
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified.',
                ], 200);
            }

            // Send verification email
            $user->notify(new VerifyEmailNotification($user));

            return response()->json([
                'message' => 'Verification email sent successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resend verification email.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    /**
     * Get current user profile.
     *
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return response()->json([
                'message' => 'User profile retrieved successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user profile.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }
}
