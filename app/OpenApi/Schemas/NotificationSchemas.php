<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationItem',
    required: ['id', 'type', 'created_at', 'read_at', 'data'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '9b7b5bd6-8f5f-4f85-9b47-7a2f7f29a2d1'),
        new OA\Property(property: 'type', type: 'string', example: 'App\\Services\\Proposals\\Notifications\\ProposalSubmittedNotification'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true, example: '2025-11-28T10:20:00.000000Z'),
        new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(
            property: 'data',
            type: 'object',
            additionalProperties: true,
            example: [
                'type' => 'proposal_submitted',
                'proposal_id' => 4,
                'title' => 'Modern Laravel at Scale',
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'NotificationsListResponse',
    required: ['items', 'unread'],
    properties: [
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/NotificationItem')
        ),
        new OA\Property(property: 'unread', type: 'integer', example: 3),
    ]
)]
#[OA\Schema(
    schema: 'NotificationsUnreadCountResponse',
    required: ['unread'],
    properties: [
        new OA\Property(property: 'unread', type: 'integer', example: 3),
    ]
)]
final class NotificationSchemas {}
