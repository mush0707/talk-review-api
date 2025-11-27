<?php

namespace App\Services\Proposals\Data;

use Spatie\LaravelData\Data;

class ReviewSearchData extends Data
{
    public function __construct(
        public ?string $search,
        public ?int $rating_min,
        public ?int $rating_max,
        public int $page = 1,
        public int $per_page = 10,
    ) {}

    public static function rules(): array
    {
        return [
            'search' => ['nullable','string','max:200'],
            'rating_min' => ['nullable','integer','min:1','max:5'],
            'rating_max' => ['nullable','integer','min:1','max:5'],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:50'],
        ];
    }
}

