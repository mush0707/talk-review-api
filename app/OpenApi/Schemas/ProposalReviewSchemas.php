<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProposalReview',
    required: ['id', 'proposal_id', 'reviewer_id', 'rating'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 10),
        new OA\Property(property: 'proposal_id', type: 'integer', example: 4),
        new OA\Property(property: 'reviewer_id', type: 'integer', example: 2),
        new OA\Property(property: 'rating', type: 'integer', example: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Great talk.', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-11-27T22:12:00.000000Z', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-11-27T22:12:00.000000Z', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ProposalReviewSearchDocumentContent',
    required: ['id', 'proposal_id', 'reviewer_id', 'rating', 'comment', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 10),
        new OA\Property(property: 'proposal_id', type: 'integer', example: 4),
        new OA\Property(property: 'reviewer_id', type: 'integer', example: 2),
        new OA\Property(property: 'rating', type: 'integer', example: 4),
        new OA\Property(property: 'comment', type: 'string', example: 'Nice structure, add more examples.', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-11-27T22:12:00+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'ProposalReviewSearchDocument',
    required: ['id', 'content'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '10'),
        new OA\Property(property: 'content', ref: '#/components/schemas/ProposalReviewSearchDocumentContent'),
    ]
)]
#[OA\Schema(
    schema: 'ProposalReviewSearchHit',
    required: ['model', 'index_name', 'document', 'highlight', 'score'],
    properties: [
        new OA\Property(property: 'model', ref: '#/components/schemas/ProposalReview'),
        new OA\Property(property: 'index_name', type: 'string', example: 'proposal_reviews'),
        new OA\Property(property: 'document', ref: '#/components/schemas/ProposalReviewSearchDocument'),
        new OA\Property(property: 'highlight', type: 'object', example: null, nullable: true),
        new OA\Property(property: 'score', type: 'number', format: 'float', example: null, nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedProposalReviewSearchHitResponse',
    required: ['current_page', 'data', 'last_page', 'links', 'path', 'per_page', 'total'],
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProposalReviewSearchHit')
        ),
        new OA\Property(property: 'first_page_url', type: 'string', example: 'http://localhost:8080/api/proposals/4/reviews?page=1', nullable: true),
        new OA\Property(property: 'from', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'last_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page_url', type: 'string', example: 'http://localhost:8080/api/proposals/4/reviews?page=1', nullable: true),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LaravelPaginationLink')
        ),
        new OA\Property(property: 'next_page_url', type: 'string', example: null, nullable: true),
        new OA\Property(property: 'path', type: 'string', example: 'http://localhost:8080/api/proposals/4/reviews'),
        new OA\Property(property: 'per_page', type: 'integer', example: 10),
        new OA\Property(property: 'prev_page_url', type: 'string', example: null, nullable: true),
        new OA\Property(property: 'to', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'total', type: 'integer', example: 2),
    ]
)]
#[OA\Schema(
    schema: 'ProposalReviewUpsertRequest',
    required: ['rating'],
    properties: [
        new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1, example: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Great talk.', nullable: true),
    ]
)]
final class ProposalReviewSchemas {}
