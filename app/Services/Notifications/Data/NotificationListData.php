<?php

namespace App\Services\Notifications\Data;

use Spatie\LaravelData\Data;

class NotificationListData extends Data
{
    public function __construct(
        public int $limit = 20,
        public ?string $search = null
    ) {}

    public static function rules(): array
    {
        return [
            'limit' => 'sometimes|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ];
    }
}
