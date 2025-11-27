<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tags', description: 'Tags library (autocomplete)')]
final class TagPaths
{
    #[OA\Get(
        path: '/api/tags',
        operationId: 'tagsIndex',
        summary: 'List tags (autocomplete / library)',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'tech'),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20), example: 20),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tags list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Tag')
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden / Email not verified'),
        ]
    )]
    public function index(): void {}
}
