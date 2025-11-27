<?php

namespace App\Services\Tags;

use App\Models\Tag;
use Illuminate\Support\Str;
class TagLibraryService
{
    /**
     * @param string[] $names
     * @return Tag[]
     */
    public function firstOrCreateByNames(array $names): array
    {
        $out = [];

        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '') continue;

            $slug = Str::slug($name);

            $out[] = Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'slug' => $slug]
            );
        }

        return $out;
    }
}
