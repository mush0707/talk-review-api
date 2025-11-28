<?php

namespace Tests\Feature\Proposals;

use AllowDynamicProperties;
use App\Models\Proposal;
use App\Models\User;
use App\Services\Proposals\Enums\ProposalStatus;
use App\Services\Proposals\Notifications\ProposalReviewedNotification;
use App\Services\Proposals\Notifications\ProposalStatusChangedNotification;
use App\Services\Proposals\Notifications\ProposalSubmittedNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

#[AllowDynamicProperties]
class ProposalFlowTest extends TestCase
{
    use RefreshDatabase;

    private string $uniq;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uniq = (string) now()->timestamp . '_' . bin2hex(random_bytes(4));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        config()->set('scout.driver', 'database');
        config()->set('scout.queue', false);

        // avoid accidental cookie/session “stateful sanctum” behavior in tests
        config()->set('sanctum.stateful', []);
        config()->set('session.driver', 'array');
    }

    public function test_speaker_can_create_proposal_with_pdf_and_tags_and_notifies_reviewers_admins(): void
    {
        Notification::fake();

        config()->set('filesystems.default', 'local');
        Storage::fake('local');

        $speaker = $this->verifiedUserWithRole('speaker');
        $reviewer = $this->verifiedUserWithRole('reviewer');
        $admin = $this->verifiedUserWithRole('admin');

        $token = $speaker->createToken('t')->plainTextToken;

        $proposalId = $this->createProposalViaApi($token, [
            'title' => 'Modern Laravel at Scale ' . $this->uniq,
            'description' => 'This talk covers architecture, queues, and caching in production.',
            'tag_names' => ['Technology ' . $this->uniq, 'Business ' . $this->uniq],
        ]);

        $proposal = Proposal::query()->with('tags')->findOrFail($proposalId);

        $this->assertSame(ProposalStatus::Pending, $proposal->status);
        $this->assertSame('Modern Laravel at Scale ' . $this->uniq, $proposal->title);
        $this->assertCount(2, $proposal->tags);
        $this->assertNotNull($proposal->attachment_path);

        Notification::assertSentTo($reviewer, ProposalSubmittedNotification::class);
        Notification::assertSentTo($admin, ProposalSubmittedNotification::class);
        Notification::assertNotSentTo($speaker, ProposalSubmittedNotification::class);
    }

    public function test_speaker_cannot_view_other_speakers_proposal(): void
    {
        Notification::fake();

        $speakerA = $this->verifiedUserWithRole('speaker');
        $speakerB = $this->verifiedUserWithRole('speaker');

        $tokenA = $speakerA->createToken('t')->plainTextToken;
        $tokenB = $speakerB->createToken('t')->plainTextToken;

        $proposalId = $this->createProposalViaApi($tokenA, [
            'title' => 'A title ' . $this->uniq,
            'description' => 'A long enough description for creation.',
            'tag_names' => ['Technology ' . $this->uniq],
        ]);

        $this->resetHttpState();

        $this->getJson("/api/proposals/{$proposalId}", $this->authHeaders($tokenB))
            ->assertStatus(403);
    }

    public function test_reviewer_can_upsert_review_notifies_owner_and_admin(): void
    {
        Notification::fake();

        $speaker = $this->verifiedUserWithRole('speaker');
        $reviewer = $this->verifiedUserWithRole('reviewer');
        $admin = $this->verifiedUserWithRole('admin');

        $tokenSpeaker = $speaker->createToken('t')->plainTextToken;
        $tokenReviewer = $reviewer->createToken('t')->plainTextToken;

        $proposalId = $this->createProposalViaApi($tokenSpeaker, [
            'title' => 'Review this proposal ' . $this->uniq,
            'description' => 'Enough text to be valid in real life.',
            'tag_names' => ['Technology ' . $this->uniq],
        ]);

        $this->resetHttpState();

        $this->putJson("/api/proposals/{$proposalId}/reviews/me", [
            'rating' => 5,
            'comment' => 'Great topic and structure. ' . $this->uniq,
        ], $this->authHeaders($tokenReviewer))
            ->assertStatus(201)
            ->assertJsonPath('rating', 5);

        Notification::assertSentTo($speaker, ProposalReviewedNotification::class);
        Notification::assertSentTo($admin, ProposalReviewedNotification::class);
        Notification::assertNotSentTo($reviewer, ProposalReviewedNotification::class);
    }

    public function test_admin_can_change_status_notifies_owner_and_reviewers_who_reviewed(): void
    {
        Notification::fake();

        $speaker = $this->verifiedUserWithRole('speaker');
        $reviewer = $this->verifiedUserWithRole('reviewer');
        $admin = $this->verifiedUserWithRole('admin');

        $tokenSpeaker = $speaker->createToken('t')->plainTextToken;
        $tokenReviewer = $reviewer->createToken('t')->plainTextToken;
        $tokenAdmin = $admin->createToken('t')->plainTextToken;

        $proposalId = $this->createProposalViaApi($tokenSpeaker, [
            'title' => 'Status change ' . $this->uniq,
            'description' => 'Enough text to be valid in real life.',
            'tag_names' => ['Technology ' . $this->uniq],
        ]);

        $this->resetHttpState();

        $this->putJson("/api/proposals/{$proposalId}/reviews/me", [
            'rating' => 5,
            'comment' => 'Reviewed before status change. ' . $this->uniq,
        ], $this->authHeaders($tokenReviewer))
            ->assertStatus(201);

        $this->resetHttpState();

        $this->patchJson("/api/proposals/{$proposalId}/status", [
            'status' => 'approved',
        ], $this->authHeaders($tokenAdmin))
            ->assertNoContent();

        Notification::assertSentTo($speaker, ProposalStatusChangedNotification::class);
        Notification::assertSentTo($reviewer, ProposalStatusChangedNotification::class);
    }

    private function verifiedUserWithRole(string $role)
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        return $user->fresh();
    }

    private function authHeaders(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    private function createProposalViaApi(string $token, array $overrides = []): int
    {
        $payload = array_merge([
            'title' => 'Proposal ' . $this->uniq,
            'description' => 'A long enough description for creation.',
            'tag_names' => ['Technology ' . $this->uniq],
            'file' => UploadedFile::fake()->create('deck.pdf', 100, 'application/pdf'),
        ], $overrides);

        $this->resetHttpState();

        $res = $this->post('/api/proposals', $payload, $this->authHeaders($token));

        $res->assertStatus(201);

        return (int) $res->json('id');
    }

    protected function resetHttpState(): void
    {
        $this->defaultHeaders = [];
        $this->defaultCookies = [];
        $this->defaultServerVariables = [];

        try {
            Auth::logout();
        } catch (\Throwable $e) {
        }

        app('auth')->forgetGuards();
    }
}
