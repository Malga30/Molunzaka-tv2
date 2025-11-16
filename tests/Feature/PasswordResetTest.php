<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    /**
     * Test user can request password reset.
     *
     * @test
     */
    public function user_can_request_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Password reset link sent to your email.',
            ]);
    }

    /**
     * Test password reset request fails with non-existent email.
     *
     * @test
     */
    public function password_reset_request_fails_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test password reset request fails with invalid email format.
     *
     * @test
     */
    public function password_reset_request_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test user can reset password with valid token.
     *
     * @test
     */
    public function user_can_reset_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Generate a password reset token
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Password has been reset successfully.',
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        // Verify old password doesn't work
        $this->assertFalse(Hash::check('OldPassword123!', $user->password));
    }

    /**
     * Test password reset fails with invalid token.
     *
     * @test
     */
    public function password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test password reset fails with mismatched passwords.
     *
     * @test
     */
    public function password_reset_fails_with_mismatched_passwords(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test password reset fails with weak password.
     *
     * @test
     */
    public function password_reset_fails_with_weak_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'john@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test user can verify email.
     *
     * @test
     */
    public function user_can_verify_email(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->postJson("/api/auth/email/verify/{$user->id}/" . hash('sha256', $user->email), []);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Email verified successfully.',
            ]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Test email verification fails with invalid hash.
     *
     * @test
     */
    public function email_verification_fails_with_invalid_hash(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->postJson("/api/auth/email/verify/{$user->id}/invalid-hash", []);

        $response->assertStatus(403);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    /**
     * Test user can resend verification email.
     *
     * @test
     */
    public function user_can_resend_verification_email(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/email/resend-verification');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Verification email sent successfully.',
            ]);
    }

    /**
     * Test user cannot resend verification when not authenticated.
     *
     * @test
     */
    public function unauthenticated_user_cannot_resend_verification(): void
    {
        $response = $this->postJson('/api/auth/email/resend-verification');

        $response->assertStatus(401);
    }
}
