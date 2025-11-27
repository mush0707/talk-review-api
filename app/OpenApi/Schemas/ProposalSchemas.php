<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Proposal',
    required: ['id', 'speaker_id', 'title', 'description', 'status'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 10),
        new OA\Property(property: 'speaker_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Modern Laravel at Scale'),
        new OA\Property(property: 'description', type: 'string', example: 'We will discuss queues, caching, and architecture...'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'pending'),
        new OA\Property(property: 'attachment_path', type: 'string', example: 'proposals/abc123.pdf', nullable: true),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Tag'),
            nullable: true
        ),
        new OA\Property(property: 'reviews_count', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-11-27T12:00:00+00:00', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-11-27T12:10:00+00:00', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ProposalCreateRequest',
    required: ['title', 'description'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Modern Laravel at Scale'),
        new OA\Property(property: 'description', type: 'string', example: 'We will discuss queues, caching, and architecture...'),
        new OA\Property(
            property: 'tag_ids',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2],
            nullable: true
        ),
        new OA\Property(
            property: 'tag_names',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Technology', 'Business'],
            nullable: true
        ),
        new OA\Property(
            property: 'file',
            description: 'PDF only, max 4MB',
            type: 'string',
            format: 'binary',
            nullable: true
        ),
    ]
)]
#[OA\Schema(
    schema: 'ProposalStatusChangeRequest',
    required: ['status'],
    properties: [
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'approved'),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedProposalResponse',
    required: ['data', 'meta'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Proposal')
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]
)]
final class ProposalSchemas {}
