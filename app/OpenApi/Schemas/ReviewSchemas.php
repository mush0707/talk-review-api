<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProposalReview',
    required: ['id', 'proposal_id', 'reviewer_id', 'rating'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 99),
        new OA\Property(property: 'proposal_id', type: 'integer', example: 10),
        new OA\Property(property: 'reviewer_id', type: 'integer', example: 5),
        new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1, example: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Strong topic, good structure.', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-11-27T12:30:00+00:00', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-11-27T12:35:00+00:00', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ReviewUpsertRequest',
    required: ['rating'],
    properties: [
        new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1, example: 4),
        new OA\Property(property: 'comment', type: 'string', example: 'Nice, but needs more examples.', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedReviewResponse',
    required: ['data', 'meta'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProposalReview')
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]
)]
final class ReviewSchemas {}
