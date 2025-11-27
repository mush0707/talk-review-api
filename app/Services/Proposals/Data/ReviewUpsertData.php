<?php

namespace App\Services\Proposals\Data;

use Spatie\LaravelData\Data;

class ReviewUpsertData extends Data
{
    public function __construct(
        public int $rating,
        public ?string $comment,
    ) {}

    public static function rules(): array
    {
        return [
            'rating' => ['required','integer','min:1','max:5'],
            'comment' => ['nullable','string','max:2000'],
        ];
    }
}
