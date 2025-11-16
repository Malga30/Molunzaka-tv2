<?php

namespace Tests\Unit\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class EmailVerificationServiceTest extends TestCase
{
    /**
     * Test email verification notification sent.
     *
     * @test
     */
    public function email_verification_notification_can_be_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->sendEmailVerificationNotification();

        Mail::assertSent(VerifyEmail::class);
    }

    /**
     * Test user with verified email passes verification.
     *
     * @test
     */
    public function verified_user_has_verified_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertTrue($user->hasVerifiedEmail());
    }

    /**
     * Test user without verified email fails verification check.
     *
     * @test
     */
    public function unverified_user_fails_verification_check(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertFalse($user->hasVerifiedEmail());
    }

    /**
     * Test mark email as verified sets timestamp.
     *
     * @test
     */
    public function mark_email_as_verified_sets_timestamp(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->markEmailAsVerified();

        $this->assertNotNull($user->email_verified_at);
        $this->assertInstanceOf(\DateTime::class, $user->email_verified_at);
    }

    /**
     * Test user can be queried by verification status.
     *
     * @test
     */
    public function users_can_be_filtered_by_verification_status(): void
    {
        User::factory()->create([
            'email_verified_at' => null,
        ]);

        User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unverifiedCount = User::whereNull('email_verified_at')->count();
        $verifiedCount = User::whereNotNull('email_verified_at')->count();

        $this->assertEquals(1, $unverifiedCount);
        $this->assertEquals(1, $verifiedCount);
    }

    /**
     * Test verification timestamp is recent.
     *
     * @test
     */
    public function verification_timestamp_is_recent(): void
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        $this->assertTrue(
            $user->email_verified_at->diffInSeconds(now()) < 5
        );
    }
}
