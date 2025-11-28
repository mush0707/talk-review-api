<?php

namespace App\Services\Tags\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class TagSearchData extends Data
{
    public function __construct(
        public int $limit = 20,
        public ?string $search = null
    )
    {
    }

    public static function rules(): array
    {
        return [
            'search' => 'nullable|string|max:24',
            'limit' => 'int|gt:0',
        ];
    }
}
