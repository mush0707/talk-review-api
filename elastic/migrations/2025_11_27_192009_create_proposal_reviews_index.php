<?php
declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateProposalReviewsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::createIfNotExists('proposal_reviews', function (Mapping $mapping, Settings $settings) {
            $settings->index([
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ]);

            $mapping->integer('id');
            $mapping->integer('proposal_id');
            $mapping->integer('reviewer_id');

            // searchable
            $mapping->text('comment');

            // filters
            $mapping->integer('rating');

            $mapping->date('created_at');
            $mapping->date('updated_at');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('proposal_reviews');
    }
}
