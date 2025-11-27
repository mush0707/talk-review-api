<?php
declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateProposalsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Index::createIfNotExists('proposals', function (Mapping $mapping, Settings $settings) {
            $settings->index([
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ]);

            $mapping->integer('id');
            $mapping->integer('speaker_id');

            // searchable fields
            $mapping->text('title');
            $mapping->text('description');

            // filter/sort fields
            $mapping->keyword('status');

            // simplest tags shape: array of keywords (["Technology","Business"])
            $mapping->keyword('tags');

            $mapping->date('created_at');
            $mapping->date('updated_at');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('proposals');
    }
}
