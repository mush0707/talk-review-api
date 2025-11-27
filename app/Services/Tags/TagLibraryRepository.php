<?php

namespace App\Services\Tags;

use App\Models\Tag;
use App\Services\Tags\Data\TagSearchData;
use Illuminate\Support\Collection;

class TagLibraryRepository
{
    public function list(TagSearchData $data): Collection
    {
        return Tag::query()
            ->select(['id', 'name', 'slug'])
            ->when($data->search, function ($query, $search) {
                $qLower = mb_strtolower($search);

                $query->whereRaw('LOWER(name) LIKE ?', ['%' . $qLower . '%'])
                    ->orderByRaw(
                        "CASE
                            WHEN LOWER(name) LIKE ? THEN 0
                            ELSE 1
                         END",
                        [$qLower . '%']
                    )
                    ->orderBy('name');
            }, fn ($query) => $query->orderBy('name'))
            ->limit($data->limit)
            ->get();
    }
}
