<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        // avoid cookie/stateful sanctum behavior in tests
        config()->set('sanctum.stateful', []);
        config()->set('session.driver', 'array');

        // keep broadcasts safe
        config()->set('broadcasting.default', 'log');
    }

    public function test_index_returns_items_and_unread_count(): void
    {
        $user = $this->verifiedUserWithRole('admin');
        $token = $user->createToken('t')->plainTextToken;

        // Create 2 notifications; 1 read, 1 unread
        $this->createDbNotification($user, [
            'type' => 'proposal_submitted',
            'proposal_id' => 1,
            'title' => 'Test Proposal A',
        ], now()->toISOString());

        $this->createDbNotification($user, [
            'type' => 'proposal_submitted',
            'proposal_id' => 2,
            'title' => 'Test Proposal B',
        ], null);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/notifications?limit=20');

        $res->assertOk();

        $payload = $this->payload($res);

        $this->assertArrayHasKey('items', $payload);
        $this->assertArrayHasKey('unread', $payload);
        $this->assertIsArray($payload['items']);
        $this->assertIsInt($payload['unread']);

        $this->assertCount(2, $payload['items']);
        $this->assertSame(1, $payload['unread']);
    }

    public function test_index_supports_search(): void
    {
        $user = $this->verifiedUserWithRole('admin');
        $token = $user->createToken('t')->plainTextToken;

        $this->createDbNotification($user, [
            'type' => 'proposal_submitted',
            'proposal_id' => 11,
            'title' => 'UniqueTitle_ABC',
        ], null);

        $this->createDbNotification($user, [
            'type' => 'proposal_submitted',
            'proposal_id' => 22,
            'title' => 'OtherTitle_XYZ',
        ], null);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/notifications?limit=20&search=UniqueTitle_ABC');

        $res->assertOk();

        $payload = $this->payload($res);
        $items = $payload['items'] ?? [];

        $this->assertCount(1, $items);
        $this->assertSame('UniqueTitle_ABC', data_get($items[0], 'data.title'));
    }

    public function test_read_all_marks_all_unread_as_read_and_returns_ok(): void
    {
        $user = $this->verifiedUserWithRole('admin');
        $token = $user->createToken('t')->plainTextToken;

        $this->createDbNotification($user, ['type' => 'x', 'title' => 'N1'], null);
        $this->createDbNotification($user, ['type' => 'y', 'title' => 'N2'], null);

        $this->assertSame(2, $user->unreadNotifications()->count());

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/notifications/read-all');

        $res->assertOk();

        $ok = $res->json('ok');
        if ($ok === null) {
            $ok = $res->json('data.ok');
        }
        $this->assertTrue((bool) $ok);

        $user->refresh();
        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_unread_count_returns_number(): void
    {
        $user = $this->verifiedUserWithRole('admin');
        $token = $user->createToken('t')->plainTextToken;

        $this->createDbNotification($user, ['type' => 'x', 'title' => 'N1'], now()->toISOString()); // read
        $this->createDbNotification($user, ['type' => 'y', 'title' => 'N2'], null); // unread

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/notifications/unread-count');

        $res->assertOk();

        $payload = $this->payload($res);
        $this->assertArrayHasKey('unread', $payload);
        $this->assertIsInt($payload['unread']);
        $this->assertSame(1, $payload['unread']);
    }

    private function payload($response): array
    {
        $json = $response->json();
        if (is_array($json) && array_key_exists('data', $json) && is_array($json['data'])) {
            return $json['data'];
        }
        return is_array($json) ? $json : [];
    }

    private function verifiedUserWithRole(string $role)
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);
        return $user->fresh();
    }

    private function createDbNotification(User $user, array $data, ?string $readAtIso): void
    {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            // IMPORTANT: Laravel stores the notification class name here normally,
            'type' => 'Tests\\DatabaseNotification',
            'data' => $data,
            'read_at' => $readAtIso,
        ]);
    }
}
