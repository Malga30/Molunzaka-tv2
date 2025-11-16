<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\RoleAssignmentService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Test user has full name attribute.
     *
     * @test
     */
    public function user_has_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /**
     * Test user can check if email is verified.
     *
     * @test
     */
    public function user_can_check_email_verification_status(): void
    {
        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertFalse($unverifiedUser->hasVerifiedEmail());
        $this->assertTrue($verifiedUser->hasVerifiedEmail());
    }

    /**
     * Test user can mark email as verified.
     *
     * @test
     */
    public function user_can_mark_email_as_verified(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertNull($user->email_verified_at);

        $user->markEmailAsVerified();
        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Test user has many profiles relationship.
     *
     * @test
     */
    public function user_has_many_profiles(): void
    {
        $user = User::factory()->create();

        $user->profiles()->create([
            'name' => 'Profile 1',
            'avatar' => 'avatar-1.png',
        ]);

        $user->profiles()->create([
            'name' => 'Profile 2',
            'avatar' => 'avatar-2.png',
        ]);

        $this->assertEquals(2, $user->profiles()->count());
    }

    /**
     * Test user can check if max profiles reached.
     *
     * @test
     */
    public function user_can_check_if_max_profiles_reached(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasMaxProfiles());

        for ($i = 1; $i <= 5; $i++) {
            $user->profiles()->create([
                'name' => "Profile $i",
                'avatar' => "avatar-$i.png",
            ]);
        }

        $this->assertTrue($user->hasMaxProfiles());
    }

    /**
     * Test user can get remaining profile slots.
     *
     * @test
     */
    public function user_can_get_remaining_profile_slots(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(5, $user->remainingProfiles());

        $user->profiles()->create([
            'name' => 'Profile 1',
            'avatar' => 'avatar-1.png',
        ]);

        $this->assertEquals(4, $user->remainingProfiles());

        for ($i = 2; $i <= 5; $i++) {
            $user->profiles()->create([
                'name' => "Profile $i",
                'avatar' => "avatar-$i.png",
            ]);
        }

        $this->assertEquals(0, $user->remainingProfiles());
    }

    /**
     * Test user can get current profile.
     *
     * @test
     */
    public function user_can_get_current_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'Main Profile',
            'avatar' => 'avatar.png',
        ]);

        session(["user_profile_{$user->id}" => $profile->id]);

        $currentProfile = $user->getCurrentProfile();

        $this->assertEquals($profile->id, $currentProfile->id);
    }

    /**
     * Test user can set current profile.
     *
     * @test
     */
    public function user_can_set_current_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'Main Profile',
            'avatar' => 'avatar.png',
        ]);

        $result = $user->setCurrentProfile($profile);

        $this->assertTrue($result);
        $this->assertEquals($profile->id, session("user_profile_{$user->id}"));
    }

    /**
     * Test user cannot set profile belonging to another user.
     *
     * @test
     */
    public function user_cannot_set_profile_of_another_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $profile = $user2->profiles()->create([
            'name' => 'User 2 Profile',
            'avatar' => 'avatar.png',
        ]);

        $result = $user1->setCurrentProfile($profile);

        $this->assertFalse($result);
    }

    /**
     * Test user has roles relationship from Spatie.
     *
     * @test
     */
    public function user_has_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $this->assertTrue($user->hasRole('Subscriber'));
        $this->assertCount(1, $user->getRoleNames());
    }

    /**
     * Test user can have multiple roles.
     *
     * @test
     */
    public function user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['Subscriber', 'Production House']);

        $this->assertTrue($user->hasRole('Subscriber'));
        $this->assertTrue($user->hasRole('Production House'));
        $this->assertCount(2, $user->getRoleNames());
    }

    /**
     * Test user can check any role.
     *
     * @test
     */
    public function user_can_check_any_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        $this->assertTrue($user->hasAnyRole(['Subscriber', 'Super Admin']));
        $this->assertFalse($user->hasAnyRole(['Super Admin', 'Production House']));
    }

    /**
     * Test user can check permissions.
     *
     * @test
     */
    public function user_can_check_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Subscriber');

        // Subscriber has stream_content permission
        $this->assertTrue($user->can('stream_content'));
        $this->assertFalse($user->can('upload_video'));
    }
}
