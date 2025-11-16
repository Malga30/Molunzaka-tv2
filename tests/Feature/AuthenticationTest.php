<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Test user can register successfully.
     *
     * @test
     */
    public function user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'date_of_birth',
                        'created_at',
                    ],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Verify user has Subscriber role
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('Subscriber'));
    }

    /**
     * Test registration fails with invalid email.
     *
     * @test
     */
    public function registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test registration fails with duplicate email.
     *
     * @test
     */
    public function registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * Test registration fails with weak password.
     *
     * @test
     */
    public function registration_fails_with_weak_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test registration fails with mismatched password confirmation.
     *
     * @test
     */
    public function registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    /**
     * Test user can login with correct credentials.
     *
     * @test
     */
    public function user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                    ],
                    'token',
                    'token_type',
                ],
            ]);
    }

    /**
     * Test login fails with invalid credentials.
     *
     * @test
     */
    public function login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * Test login fails with wrong password.
     *
     * @test
     */
    public function login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Invalid credentials.',
            ]);
    }

    /**
     * Test login fails when email is not verified.
     *
     * @test
     */
    public function login_fails_when_email_not_verified(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Please verify your email address before logging in.',
            ]);
    }

    /**
     * Test authenticated user can access me endpoint.
     *
     * @test
     */
    public function authenticated_user_can_get_current_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'roles',
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot access me endpoint.
     *
     * @test
     */
    public function unauthenticated_user_cannot_get_current_user(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJsonFragment([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test user can logout.
     *
     * @test
     */
    public function user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Logged out successfully.',
            ]);

        // Verify token is revoked
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test logout all sessions.
     *
     * @test
     */
    public function user_can_logout_all_sessions(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('token-1')->plainTextToken;
        $token2 = $user->createToken('token-2')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/auth/logout-all');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Logged out from all devices successfully.',
            ]);

        // Verify both tokens are revoked
        $this->withHeader('Authorization', "Bearer $token1")
            ->getJson('/api/auth/me')
            ->assertStatus(401);

        $this->withHeader('Authorization', "Bearer $token2")
            ->getJson('/api/auth/me')
            ->assertStatus(401);
    }

    /**
     * Test user cannot logout when not authenticated.
     *
     * @test
     */
    public function unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }
}
