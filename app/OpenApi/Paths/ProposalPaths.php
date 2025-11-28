<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Proposals', description: 'Talk proposals')]
final class ProposalPaths
{
    #[OA\Get(
        path: '/api/proposals',
        operationId: 'proposalsIndex',
        summary: 'Search/list proposals (role-scoped)',
        security: [['bearerAuth' => []]],
        tags: ['Proposals'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'laravel'),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending','approved','rejected']), example: 'pending'),
            new OA\Parameter(
                name: 'tag_ids[]',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
                example: [1, 2],
                style: 'form',
                explode: true
            ),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1), example: 1),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10), example: 10),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated proposals',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedProposalSearchHitResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden / Email not verified'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/proposals',
        operationId: 'proposalsStore',
        summary: 'Create proposal (speaker only)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/ProposalCreateRequest')
            )
        ),
        tags: ['Proposals'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created proposal',
                content: new OA\JsonContent(ref: '#/components/schemas/Proposal')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden / Email not verified'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/proposals/{proposal}',
        operationId: 'proposalsShow',
        summary: 'Get proposal by id (speaker can only access own)',
        security: [['bearerAuth' => []]],
        tags: ['Proposals'],
        parameters: [
            new OA\Parameter(name: 'proposal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Proposal + attachment download link (if exists)',
                content: new OA\JsonContent(ref: '#/components/schemas/ProposalShowResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(): void {}

    #[OA\Get(
        path: '/api/proposals/{proposal}/attachment',
        operationId: 'proposalsDownloadAttachment',
        summary: 'Download proposal attachment via temporary signed URL',
        tags: ['Proposals'],
        parameters: [
            new OA\Parameter(name: 'proposal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 4),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF file stream',
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(response: 403, description: 'Invalid/expired signature'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function downloadAttachment(): void {}

    #[OA\Patch(
        path: '/api/proposals/{proposal}/status',
        operationId: 'proposalsChangeStatus',
        summary: 'Change proposal status (admin only)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ProposalStatusChangeRequest')
        ),
        tags: ['Proposals'],
        parameters: [
            new OA\Parameter(name: 'proposal', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 10),
        ],
        responses: [
            new OA\Response(response: 204, description: 'No content'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function changeStatus(): void {}
}
