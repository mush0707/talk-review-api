<?php

namespace App\Services\Proposals\Data;

use App\Services\Proposals\Enums\ProposalStatus;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Data;

class ProposalStatusChangeData extends Data
{
    public function __construct(public string $status)
    {
    }

    public static function rules(): array
    {
        return ['status' => ['required', new Enum(ProposalStatus::class)]];
    }
}
