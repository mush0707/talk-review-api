<?php

namespace Tests\Feature\Proposals;

use App\Models\Proposal;
use App\Models\Tag;
use App\Models\User;
use App\Services\Proposals\Notifications\ProposalReviewedNotification;
use App\Services\Proposals\Notifications\ProposalStatusChangedNotification;
use App\Services\Proposals\Notifications\ProposalSubmittedNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\InteractsWithElasticsearch;
use Tests\TestCase;

class ProposalFlowTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithElasticsearch;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->skipIfElasticDown();

        // keep indices clean between tests
        $this->esDeleteAll('proposals');
        $this->esDeleteAll('proposal_reviews');
    }

    public function test_speaker_can_create_proposal_with_pdf_and_tags_and_notifies_reviewers_admins(): void
    {
        Storage::fake('public');
        Notification::fake();

        $speaker = User::factory()->create(['email_verified_at' => now()]);
        $speaker->assignRole('speaker');

        $reviewer = User::factory()->create(['email_verified_at' => now()]);
        $reviewer->assignRole('reviewer');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $token = $speaker->createToken('t')->plainTextToken;

        $payload = [
            'title' => 'Modern Laravel at Scale',
            'description' => 'This talk covers architecture, queues, and caching in production.',
            'tag_names' => ['Technology', 'Business'],
            'file' => UploadedFile::fake()->create('deck.pdf', 100, 'application/pdf'),
        ];

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/proposals', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('title', 'Modern Laravel at Scale');

        $proposalId = $res->json('id');

        $proposal = Proposal::query()->with('tags')->findOrFail($proposalId);
        $this->assertCount(2, $proposal->tags);

        $this->assertNotNull($proposal->attachment_path);
        Storage::disk('public')->assertExists($proposal->attachment_path);

        // ES refresh so search endpoints won’t flake if you later test them
        $this->esRefresh('proposals');

        Notification::assertSentTo($reviewer, ProposalSubmittedNotification::class);
        Notification::assertSentTo($admin, ProposalSubmittedNotification::class);
        Notification::assertNotSentTo($speaker, ProposalSubmittedNotification::class);
    }

    public function test_speaker_cannot_view_other_speakers_proposal(): void
    {
        Notification::fake();

        $speakerA = User::factory()->create(['email_verified_at' => now()]);
        $speakerA->assignRole('speaker');

        $speakerB = User::factory()->create(['email_verified_at' => now()]);
        $speakerB->assignRole('speaker');

        $proposal = Proposal::query()->create([
            'speaker_id' => $speakerA->id,
            'title' => 'A title',
            'description' => 'A long enough description for creation.',
            'status' => 'pending',
            'attachment_path' => null,
        ]);

        $proposal->searchable();
        $this->esRefresh('proposals');

        $tokenB = $speakerB->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson("/api/proposals/{$proposal->id}")
            ->assertStatus(403);
    }

    public function test_reviewer_can_upsert_review_notifies_owner_and_admin_then_admin_status_change_notifies_owner_and_reviewer(): void
    {
        Notification::fake();

        $speaker = User::factory()->create(['email_verified_at' => now()]);
        $speaker->assignRole('speaker');

        $reviewer = User::factory()->create(['email_verified_at' => now()]);
        $reviewer->assignRole('reviewer');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $proposal = Proposal::query()->create([
            'speaker_id' => $speaker->id,
            'title' => 'Review this proposal',
            'description' => 'Enough text to be valid in real life.',
            'status' => 'pending',
            'attachment_path' => null,
        ]);

        // attach a tag just to reflect real behavior
        $tag = Tag::query()->create(['name' => 'Technology', 'slug' => 'technology']);
        $proposal->tags()->sync([$tag->id]);

        $proposal->searchable();
        $this->esRefresh('proposals');

        // reviewer upsert review
        $tokenReviewer = $reviewer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenReviewer}")
            ->putJson("/api/proposals/{$proposal->id}/reviews/me", [
                'rating' => 5,
                'comment' => 'Great topic and structure.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('rating', 5);

        $this->esRefresh('proposal_reviews');

        Notification::assertSentTo($speaker, ProposalReviewedNotification::class);
        Notification::assertSentTo($admin, ProposalReviewedNotification::class);
        Notification::assertNotSentTo($reviewer, ProposalReviewedNotification::class);

        // admin changes status => notify owner + reviewers who reviewed
        $tokenAdmin = $admin->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenAdmin}")
            ->patchJson("/api/proposals/{$proposal->id}/status", [
                'status' => 'approved',
            ])
            ->assertNoContent();

        Notification::assertSentTo($speaker, ProposalStatusChangedNotification::class);
        Notification::assertSentTo($reviewer, ProposalStatusChangedNotification::class);
    }

    public function test_reviews_list_is_paginated_and_searchable_by_comment_and_rating(): void
    {
        Notification::fake();

        $speaker = User::factory()->create(['email_verified_at' => now()]);
        $speaker->assignRole('speaker');

        $reviewer = User::factory()->create(['email_verified_at' => now()]);
        $reviewer->assignRole('reviewer');

        $proposal = Proposal::query()->create([
            'speaker_id' => $speaker->id,
            'title' => 'Search reviews',
            'description' => 'Description that is long enough.',
            'status' => 'pending',
            'attachment_path' => null,
        ]);

        $proposal->searchable();
        $this->esRefresh('proposals');

        $tokenReviewer = $reviewer->createToken('t')->plainTextToken;

        // create 2 reviews via upsert for this reviewer + another reviewer
        $this->withHeader('Authorization', "Bearer {$tokenReviewer}")
            ->putJson("/api/proposals/{$proposal->id}/reviews/me", [
                'rating' => 5,
                'comment' => 'Excellent and practical.',
            ])
            ->assertStatus(201);

        $anotherReviewer = User::factory()->create(['email_verified_at' => now()]);
        $anotherReviewer->assignRole('reviewer');
        $tokenAnother = $anotherReviewer->createToken('t')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenAnother}")
            ->putJson("/api/proposals/{$proposal->id}/reviews/me", [
                'rating' => 3,
                'comment' => 'Good, but needs more depth.',
            ])
            ->assertStatus(201);

        $this->esRefresh('proposal_reviews');

        // list reviews with search filter
        $res = $this->withHeader('Authorization', "Bearer {$tokenReviewer}")
            ->getJson("/api/proposals/{$proposal->id}/reviews?search=excellent&rating_min=5&per_page=10&page=1");

        $res->assertOk()
            ->assertJsonStructure(['data', 'meta']);

        $this->assertGreaterThanOrEqual(1, count($res->json('data')));
        $this->assertSame(1, $res->json('meta.current_page'));
    }
}
