<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Tag',
    required: ['id', 'name', 'slug'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Technology'),
        new OA\Property(property: 'slug', type: 'string', example: 'technology'),
    ]
)]
final class TagSchemas {}
