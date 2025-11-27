<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resends_verification_email_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/email/verification-notification')
            ->assertOk()
            ->assertJson(['ok' => true]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_returns_422_if_already_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/email/verification-notification')
            ->assertStatus(422);
    }
}
