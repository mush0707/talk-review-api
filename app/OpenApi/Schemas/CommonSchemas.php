<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LaravelPaginationLink',
    required: ['url', 'label', 'active'],
    properties: [
        new OA\Property(property: 'url', type: 'string', example: 'http://localhost:8080/api/proposals?page=1', nullable: true),
        new OA\Property(property: 'label', type: 'string', example: '1'),
        new OA\Property(property: 'page', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'active', type: 'boolean', example: true),
    ]
)]
final class CommonSchemas {}
