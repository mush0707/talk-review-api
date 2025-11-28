<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Notifications', description: 'User notifications (database + realtime broadcast)')]
final class NotificationPaths
{
    #[OA\Get(
        path: '/api/notifications',
        operationId: 'notificationsIndex',
        summary: 'List latest notifications for current user',
        description: 'Returns latest notifications and unread count. Supports optional search.',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, minimum: 1),
                example: 20
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
                example: 'proposal'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifications list + unread count',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationsListResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden / Email not verified'),
        ]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/notifications/read-all',
        operationId: 'notificationsReadAll',
        summary: 'Mark all notifications as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/GenericOkResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden / Email not verified'),
        ]
    )]
    public function readAll(): void {}

    #[OA\Get(
        path: '/api/notifications/unread-count',
        operationId: 'notificationsUnreadCount',
        summary: 'Get unread notifications count',
        description: 'Use this for bell badge refresh (e.g., on every page refresh).',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread count',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationsUnreadCountResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden / Email not verified'),
        ]
    )]
    public function unreadCount(): void {}
}
