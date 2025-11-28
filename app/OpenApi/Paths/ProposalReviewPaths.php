<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'ProposalReviews', description: 'Proposal reviews')]
final class ProposalReviewPaths
{
    #[OA\Get(
        path: '/api/proposals/{proposal}/reviews',
        operationId: 'reviewsIndex',
        summary: 'List reviews for a proposal (paginated, searchable)',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'proposal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'great'),
            new OA\Parameter(name: 'rating_min', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 5), example: 3),
            new OA\Parameter(name: 'rating_max', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 5), example: 5),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1), example: 1),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10), example: 10),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated reviews',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedProposalReviewSearchHitResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function index(): void {}

    #[OA\Put(
        path: '/api/proposals/{proposal}/reviews/me',
        operationId: 'reviewsUpsertMine',
        summary: 'Upsert current reviewer review for proposal',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ProposalReviewUpsertRequest')
        ),
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'proposal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created/updated review',
                content: new OA\JsonContent(ref: '#/components/schemas/ProposalReview')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function upsert(): void {}
}
