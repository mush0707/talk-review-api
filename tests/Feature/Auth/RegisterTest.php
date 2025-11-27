<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_user_assigns_role_sends_verification_email_and_returns_flags(): void
    {
        Notification::fake();
        Role::findOrCreate('speaker');

        $payload = [
            'provider' => 'local',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'speaker',
        ];

        $res = $this->postJson('/api/auth/register', $payload);

        $res->assertOk()
            ->assertJsonPath('must_verify_email', true)
            ->assertJsonPath('email_verified', false)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'role'],
                'must_verify_email',
                'email_verified',
            ]);

        $user = User::where('email', $payload['email'])->firstOrFail();

        $this->assertTrue($user->hasRole('speaker'));
        $this->assertFalse($user->hasVerifiedEmail());

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_fails_when_role_does_not_exist(): void
    {
        $payload = [
            'provider' => 'local',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'not_exists',
        ];

        $this->postJson('/api/auth/register', $payload)->assertStatus(422);
    }

    public function test_registration_fails_when_user_already_exists(): void
    {
        Role::findOrCreate('speaker');

        $existingEmail = fake()->unique()->safeEmail();
        User::factory()->create(['email' => $existingEmail]);

        $payload = [
            'provider' => 'local',
            'name' => fake()->name(),
            'email' => $existingEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'speaker',
        ];

        $this->postJson('/api/auth/register', $payload)->assertStatus(422);
    }
}
