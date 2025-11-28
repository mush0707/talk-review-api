<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Notifications\Data\NotificationListData;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository
{
    public function list(NotificationListData $data): array
    {
        $q = trim((string) ($data->search ?? ''));

        $query = request()->user()->notifications()->latest();

        if ($q !== '') {
            // Search inside JSON 'data' (works on MySQL 8+)
            // Adjust searchable keys if you want more fields
            $query->where(function ($w) use ($q) {
                $w->where('type', 'like', '%' . $q . '%')
                    ->orWhere('data->type', 'like', '%' . $q . '%')
                    ->orWhere('data->title', 'like', '%' . $q . '%')
                    ->orWhere('data->comment', 'like', '%' . $q . '%')
                    ->orWhere('data->status', 'like', '%' . $q . '%');
            });
        }

        return $query
            ->limit($data->limit)
            ->get()
            ->map(fn ($n) => [
                'id' => (string) $n->id,
                'type' => (string) $n->type,
                'created_at' => optional($n->created_at)->toISOString(),
                'read_at' => optional($n->read_at)?->toISOString(),
                'data' => $n->data ?? [],
            ])
            ->values()
            ->all();
    }
}
