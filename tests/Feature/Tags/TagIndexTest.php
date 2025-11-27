<?php

namespace Tests\Feature\Tags;

use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TagIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_lists_tags_for_authenticated_verified_user(): void
    {
        Tag::query()->create(['name' => 'Technology', 'slug' => 'technology']);
        Tag::query()->create(['name' => 'Health', 'slug' => 'health']);
        Tag::query()->create(['name' => 'Business', 'slug' => 'business']);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('speaker');

        $token = $user->createToken('t')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/tags');

        $res->assertOk()
            ->assertJsonCount(3);

        // because repository orders by name by default
        $this->assertSame('Business', $res->json('0.name'));
    }

    public function test_autocomplete_search_orders_prefix_first(): void
    {
        Tag::query()->create(['name' => 'Technology', 'slug' => 'technology']);
        Tag::query()->create(['name' => 'Biotech', 'slug' => 'biotech']);
        Tag::query()->create(['name' => 'Health', 'slug' => 'health']);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('reviewer');

        $token = $user->createToken('t')->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/tags?search=tech&limit=20');

        $res->assertOk();

        // "Technology" starts with "tech", should come before "Biotech" (contains)
        $this->assertSame('Technology', $res->json('0.name'));
    }
}
