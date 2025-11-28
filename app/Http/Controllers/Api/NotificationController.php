<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Services\Notifications\Data\NotificationListData;
use App\Services\Notifications\NotificationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{

    public function __construct(
        private NotificationRepository $repository
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = NotificationListData::from($request);
        return $this->success([
            'items' => $this->repository->list($data),
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return $this->success(['ok' => true]);
    }
}
