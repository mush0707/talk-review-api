<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifies_email_via_signed_link_without_auth(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->get($url)->assertStatus(302);

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_rejects_invalid_hash(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => 'wrong_hash',
            ]
        );

        $this->getJson($url)->assertStatus(403);

        $user->refresh();
        $this->assertFalse($user->hasVerifiedEmail());
    }
}
