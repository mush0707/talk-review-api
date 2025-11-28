<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Proposal',
    required: ['id', 'speaker_id', 'title', 'description', 'status'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'speaker_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Modern Laravel at Scale'),
        new OA\Property(property: 'description', type: 'string', example: 'We will discuss queues, caching, and architecture...'),
        new OA\Property(property: 'attachment_path', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'pending'),
        // Optional fields you may load on list later
        new OA\Property(
            property: 'tag_names',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
            example: ['Technology', 'Business']
        ),
        new OA\Property(property: 'reviews_count', type: 'integer', nullable: true, example: 2),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true, example: '2025-11-27T22:08:46.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true, example: '2025-11-27T22:08:46.000000Z'),
    ]
)]
#[OA\Schema(
    schema: 'ProposalSearchDocumentContent',
    required: ['id', 'title', 'description', 'status', 'speaker_id', 'tag_ids', 'tag_names', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'title', type: 'string', example: 'Modern Laravel at Scale'),
        new OA\Property(property: 'description', type: 'string', example: 'We will discuss queues, caching, and architecture...'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'pending'),
        new OA\Property(property: 'speaker_id', type: 'integer', example: 1),
        new OA\Property(
            property: 'tag_ids',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [],
        ),
        new OA\Property(
            property: 'tag_names',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: [],
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-11-27T22:08:46+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'ProposalSearchDocument',
    required: ['id', 'content'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '4'),
        new OA\Property(property: 'content', ref: '#/components/schemas/ProposalSearchDocumentContent'),
    ]
)]
#[OA\Schema(
    schema: 'ProposalSearchHit',
    required: ['model', 'index_name', 'document', 'highlight', 'score'],
    properties: [
        new OA\Property(property: 'model', ref: '#/components/schemas/Proposal'),
        new OA\Property(property: 'index_name', type: 'string', example: 'proposals'),
        new OA\Property(property: 'document', ref: '#/components/schemas/ProposalSearchDocument'),
        new OA\Property(property: 'highlight', type: 'object', example: null, nullable: true),
        new OA\Property(property: 'score', type: 'number', format: 'float', example: null, nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedProposalSearchHitResponse',
    required: ['current_page', 'data', 'last_page', 'links', 'path', 'per_page', 'total'],
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProposalSearchHit')
        ),
        new OA\Property(property: 'first_page_url', type: 'string', example: 'http://localhost:8080/api/proposals?page=1', nullable: true),
        new OA\Property(property: 'from', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'last_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page_url', type: 'string', example: 'http://localhost:8080/api/proposals?page=1', nullable: true),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LaravelPaginationLink')
        ),
        new OA\Property(property: 'next_page_url', type: 'string', example: null, nullable: true),
        new OA\Property(property: 'path', type: 'string', example: 'http://localhost:8080/api/proposals'),
        new OA\Property(property: 'per_page', type: 'integer', example: 10),
        new OA\Property(property: 'prev_page_url', type: 'string', example: null, nullable: true),
        new OA\Property(property: 'to', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'total', type: 'integer', example: 2),
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
    schema: 'TemporaryDownloadLink',
    required: ['url', 'expires_at'],
    properties: [
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost:8080/api/proposals/4/attachment?expires=...&signature=...'),
        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', example: '2025-11-28T10:20:00+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'ProposalShowResponse',
    required: ['proposal', 'attachment'],
    properties: [
        new OA\Property(property: 'proposal', ref: '#/components/schemas/Proposal'),
        new OA\Property(property: 'attachment', ref: '#/components/schemas/TemporaryDownloadLink', nullable: true),
    ]
)]
final class ProposalSchemas {}
