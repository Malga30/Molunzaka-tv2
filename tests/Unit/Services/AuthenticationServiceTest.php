<?php

namespace Tests\Unit\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    /**
     * Test password hashing.
     *
     * @test
     */
    public function password_is_properly_hashed(): void
    {
        $user = User::factory()->create();
        $plainPassword = 'TestPassword123!';
        $user->password = Hash::make($plainPassword);
        $user->save();

        $this->assertTrue(Hash::check($plainPassword, $user->password));
        $this->assertFalse(Hash::check('WrongPassword', $user->password));
    }

    /**
     * Test user email uniqueness.
     *
     * @test
     */
    public function email_must_be_unique(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => $email]);
    }

    /**
     * Test user can be created with valid data.
     *
     * @test
     */
    public function user_can_be_created_with_valid_data(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    /**
     * Test user password cannot be retrieved.
     *
     * @test
     */
    public function password_is_not_returned_in_queries(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('TestPassword123!'),
        ]);

        // Password should be hidden in arrays
        $userData = $user->toArray();
        $this->assertArrayNotHasKey('password', $userData);
    }

    /**
     * Test user soft delete functionality.
     *
     * @test
     */
    public function user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        // User should not be retrieved in normal queries
        $this->assertNull(User::find($userId));

        // User should be retrievable with withTrashed
        $this->assertNotNull(User::withTrashed()->find($userId));

        // User should be marked as deleted
        $this->assertSoftDeleted('users', ['id' => $userId]);
    }

    /**
     * Test user can be restored after soft delete.
     *
     * @test
     */
    public function user_can_be_restored_after_soft_delete(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();
        $user->restore();

        $restoredUser = User::find($userId);
        $this->assertNotNull($restoredUser);
        $this->assertEquals($userId, $restoredUser->id);
    }

    /**
     * Test user email verification status.
     *
     * @test
     */
    public function user_email_verification_status_can_be_tracked(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasVerifiedEmail());

        $user->markEmailAsVerified();

        $this->assertTrue($user->hasVerifiedEmail());
    }

    /**
     * Test user remember token functionality.
     *
     * @test
     */
    public function user_remember_token_can_be_generated(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->remember_token);
    }
}
