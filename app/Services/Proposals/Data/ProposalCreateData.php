<?php

namespace App\Services\Proposals\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class ProposalCreateData extends Data
{
    public function __construct(
        public string $title,
        public string $description,
        /** @var int[]|null */
        public ?array $tag_ids,
        /** @var string[]|null */
        public ?array $tag_names,
        public ?UploadedFile $file,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['required','string','min:3','max:255'],
            'description' => ['required','string','min:10'],
            'tag_ids' => ['nullable','array'],
            'tag_ids.*' => ['integer','exists:tags,id'],
            'tag_names' => ['nullable','array'],
            'tag_names.*' => ['string','min:2','max:50'],
            'file' => ['nullable','file','mimetypes:application/pdf','max:4096'],
        ];
    }
}
