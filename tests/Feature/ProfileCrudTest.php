<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Tests\TestCase;

class ProfileCrudTest extends TestCase
{
    protected User $user;
    protected string $token;

    /**
     * Setup test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test user can create a profile.
     *
     * @test
     */
    public function user_can_create_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/profiles', [
                'name' => 'Main Profile',
                'avatar' => 'avatar-1.png',
                'kids_mode' => false,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'avatar',
                    'kids_mode',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'name' => 'Main Profile',
        ]);
    }

    /**
     * Test user can create up to 5 profiles.
     *
     * @test
     */
    public function user_can_create_up_to_five_profiles(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->withHeader('Authorization', "Bearer $this->token")
                ->postJson('/api/profiles', [
                    'name' => "Profile $i",
                    'avatar' => "avatar-$i.png",
                    'kids_mode' => false,
                ]);

            $response->assertStatus(201);
        }

        $this->assertEquals(5, $this->user->profiles()->count());
    }

    /**
     * Test user cannot create more than 5 profiles.
     *
     * @test
     */
    public function user_cannot_create_more_than_five_profiles(): void
    {
        // Create 5 profiles
        for ($i = 1; $i <= 5; $i++) {
            Profile::factory()->create([
                'user_id' => $this->user->id,
                'name' => "Profile $i",
            ]);
        }

        // Try to create 6th profile
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/profiles', [
                'name' => 'Sixth Profile',
                'avatar' => 'avatar-6.png',
                'kids_mode' => false,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Maximum profile limit (5) reached',
            ]);
    }

    /**
     * Test profile creation fails without authentication.
     *
     * @test
     */
    public function profile_creation_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/profiles', [
            'name' => 'Main Profile',
            'avatar' => 'avatar-1.png',
            'kids_mode' => false,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test profile creation fails with invalid data.
     *
     * @test
     */
    public function profile_creation_fails_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/profiles', [
                'name' => '',  // Empty name
                'avatar' => 'avatar-1.png',
                'kids_mode' => false,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /**
     * Test user can retrieve all profiles.
     *
     * @test
     */
    public function user_can_retrieve_all_profiles(): void
    {
        Profile::factory(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson('/api/profiles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'profiles',
                    'total',
                    'limit',
                    'remaining',
                    'current_profile_id',
                ],
            ])
            ->assertJsonFragment([
                'total' => 3,
                'limit' => 5,
                'remaining' => 2,
            ]);
    }

    /**
     * Test user can retrieve a specific profile.
     *
     * @test
     */
    public function user_can_retrieve_specific_profile(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/profiles/{$profile->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'avatar',
                    'kids_mode',
                ],
            ])
            ->assertJsonFragment([
                'id' => $profile->id,
            ]);
    }

    /**
     * Test user cannot retrieve other user's profile.
     *
     * @test
     */
    public function user_cannot_retrieve_other_user_profile(): void
    {
        $otherUser = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->getJson("/api/profiles/{$profile->id}");

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Unauthorized: You do not own this profile',
            ]);
    }

    /**
     * Test user can update profile.
     *
     * @test
     */
    public function user_can_update_profile(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
            'kids_mode' => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->putJson("/api/profiles/{$profile->id}", [
                'name' => 'New Name',
                'avatar' => 'new-avatar.png',
                'kids_mode' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'avatar',
                    'kids_mode',
                ],
            ])
            ->assertJsonFragment([
                'name' => 'New Name',
                'kids_mode' => true,
            ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'name' => 'New Name',
            'kids_mode' => true,
        ]);
    }

    /**
     * Test user cannot update other user's profile.
     *
     * @test
     */
    public function user_cannot_update_other_user_profile(): void
    {
        $otherUser = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->putJson("/api/profiles/{$profile->id}", [
                'name' => 'New Name',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test user can delete profile.
     *
     * @test
     */
    public function user_can_delete_profile(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/profiles/{$profile->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Profile deleted successfully',
            ]);

        $this->assertDatabaseMissing('profiles', [
            'id' => $profile->id,
        ]);
    }

    /**
     * Test user cannot delete other user's profile.
     *
     * @test
     */
    public function user_cannot_delete_other_user_profile(): void
    {
        $otherUser = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->deleteJson("/api/profiles/{$profile->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
        ]);
    }

    /**
     * Test user can switch to a profile.
     *
     * @test
     */
    public function user_can_switch_to_profile(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/profiles/{$profile->id}/switch");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Profile switched successfully',
            ]);

        // Verify profile is set in session
        $this->assertEquals(
            $profile->id,
            session("user_profile_{$this->user->id}")
        );
    }

    /**
     * Test user cannot switch to non-existent profile.
     *
     * @test
     */
    public function user_cannot_switch_to_nonexistent_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson('/api/profiles/99999/switch');

        $response->assertStatus(404);
    }

    /**
     * Test user can update parental controls.
     *
     * @test
     */
    public function user_can_update_parental_controls(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => $this->user->id,
            'kids_mode' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/profiles/{$profile->id}/parental-controls", [
                'rating_limit' => 'PG-13',
                'content_restrictions' => ['violence', 'explicit'],
                'screen_time_limit' => 60,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'parental_controls',
                ],
            ]);
    }

    /**
     * Test user can update profile preferences.
     *
     * @test
     */
    public function user_can_update_profile_preferences(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
            ->postJson("/api/profiles/{$profile->id}/preferences", [
                'language' => 'en',
                'subtitle_language' => 'en',
                'playback_quality' => '1080p',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'preferences',
                ],
            ]);
    }
}
