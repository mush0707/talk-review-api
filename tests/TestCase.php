<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use App\Models\Proposal;
use App\Models\ProposalReview;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // hard force: tests must not touch Elasticsearch
        config()->set('scout.driver', 'database');
        config()->set('scout.queue', false);

        // Disable syncing per Searchable model (correct way)
        Proposal::disableSearchSyncing();
        ProposalReview::disableSearchSyncing();
    }
}
