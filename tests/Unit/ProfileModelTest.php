<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Models\User;
use Tests\TestCase;

class ProfileModelTest extends TestCase
{
    /**
     * Test profile belongs to user.
     *
     * @test
     */
    public function profile_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'Test Profile',
            'avatar' => 'avatar.png',
        ]);

        $this->assertEquals($user->id, $profile->user_id);
        $this->assertEquals($user->id, $profile->user()->first()->id);
    }

    /**
     * Test profile has name.
     *
     * @test
     */
    public function profile_has_name(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'My Profile',
            'avatar' => 'avatar.png',
        ]);

        $this->assertEquals('My Profile', $profile->name);
    }

    /**
     * Test profile has avatar.
     *
     * @test
     */
    public function profile_has_avatar(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'My Profile',
            'avatar' => 'path/to/avatar.png',
        ]);

        $this->assertEquals('path/to/avatar.png', $profile->avatar);
    }

    /**
     * Test profile can be updated.
     *
     * @test
     */
    public function profile_can_be_updated(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'Original Name',
            'avatar' => 'avatar.png',
        ]);

        $profile->update([
            'name' => 'Updated Name',
            'avatar' => 'new-avatar.png',
        ]);

        $this->assertEquals('Updated Name', $profile->name);
        $this->assertEquals('new-avatar.png', $profile->avatar);
    }

    /**
     * Test profile can be deleted.
     *
     * @test
     */
    public function profile_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'Test Profile',
            'avatar' => 'avatar.png',
        ]);

        $profileId = $profile->id;
        $profile->delete();

        $this->assertNull(Profile::find($profileId));
    }

    /**
     * Test profile is timestamped.
     *
     * @test
     */
    public function profile_is_timestamped(): void
    {
        $user = User::factory()->create();
        $profile = $user->profiles()->create([
            'name' => 'Test Profile',
            'avatar' => 'avatar.png',
        ]);

        $this->assertNotNull($profile->created_at);
        $this->assertNotNull($profile->updated_at);
    }

    /**
     * Test multiple profiles for same user.
     *
     * @test
     */
    public function multiple_profiles_for_same_user(): void
    {
        $user = User::factory()->create();

        $profile1 = $user->profiles()->create([
            'name' => 'Profile 1',
            'avatar' => 'avatar1.png',
        ]);

        $profile2 = $user->profiles()->create([
            'name' => 'Profile 2',
            'avatar' => 'avatar2.png',
        ]);

        $this->assertEquals(2, $user->profiles()->count());
        $this->assertTrue($user->profiles->contains($profile1));
        $this->assertTrue($user->profiles->contains($profile2));
    }

    /**
     * Test profiles are isolated per user.
     *
     * @test
     */
    public function profiles_are_isolated_per_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $profile1 = $user1->profiles()->create([
            'name' => 'User 1 Profile',
            'avatar' => 'avatar1.png',
        ]);

        $profile2 = $user2->profiles()->create([
            'name' => 'User 2 Profile',
            'avatar' => 'avatar2.png',
        ]);

        $this->assertEquals(1, $user1->profiles()->count());
        $this->assertEquals(1, $user2->profiles()->count());
        $this->assertFalse($user1->profiles->contains($profile2));
        $this->assertFalse($user2->profiles->contains($profile1));
    }
}
