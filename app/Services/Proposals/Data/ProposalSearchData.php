<?php

namespace App\Services\Proposals\Data;

use Spatie\LaravelData\Data;

class ProposalSearchData extends Data
{
    public function __construct(
        public ?string $search,
        /** @var int[]|null */
        public ?array $tag_ids,
        public ?string $status,
        public int $page = 1,
        public int $per_page = 15,
    ) {}

    public static function rules(): array
    {
        return [
            'search' => ['nullable','string','max:200'],
            'tag_ids' => ['nullable','array'],
            'tag_ids.*' => ['integer','exists:tags,id'],
            'status' => ['nullable','in:pending,approved,rejected'],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:50'],
        ];
    }
}
