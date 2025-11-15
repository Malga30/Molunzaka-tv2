<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationService
{
    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ]);
    }

    /**
     * Authenticate a user and return a token.
     */
    public function login(User $user, string $deviceName = 'web'): array
    {
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Generate a password reset token.
     */
    public function generatePasswordResetToken(User $user): string
    {
        return Str::random(64);
    }

    /**
     * Verify email verification token.
     */
    public function verifyEmailToken(User $user, string $hash): bool
    {
        $generated_hash = hash('sha256', $user->getKey());
        return hash_equals($hash, $generated_hash);
    }

    /**
     * Invalidate all user tokens (logout all devices).
     */
    public function logout(User $user): bool
    {
        return (bool) $user->tokens()->delete();
    }

    /**
     * Invalidate a specific token (logout single device).
     */
    public function logoutToken(string $tokenId): bool
    {
        return (bool) \Laravel\Sanctum\PersonalAccessToken::find($tokenId)?->delete();
    }
}
