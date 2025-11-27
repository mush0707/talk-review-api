<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Laravel\Sanctum\PersonalAccessToken;
class LoginMeLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_flags(): void
    {
        Role::findOrCreate('speaker');

        $password = 'password123';
        $email = fake()->unique()->safeEmail();

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => null,
        ]);
        $user->assignRole('speaker');

        $res = $this->postJson('/api/auth/login', [
            'provider' => 'local',
            'email' => $email,
            'password' => $password,
        ]);

        $res->assertOk()
            ->assertJsonStructure(['token', 'user', 'must_verify_email', 'email_verified']);
    }

    public function test_me_returns_user_and_flags(): void
    {
        Role::findOrCreate('speaker');

        $user = User::factory()->create(['email_verified_at' => null]);
        $user->assignRole('speaker');

        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonStructure(['user', 'must_verify_email', 'email_verified']);
    }

    public function test_logout_deletes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['ok' => true]);
        $this->assertNull(PersonalAccessToken::findToken($token));
        app('auth')->forgetGuards(); // because guard caching in tests.
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertStatus(401);
    }
}
