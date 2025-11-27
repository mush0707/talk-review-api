<?php

namespace App\Services\Auth\Data;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $role,
    ) {}

    public static function fromModel(User $user): self
    {
        $user->loadMissing('roles');

        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->roles?->first()?->name ?? null,
        );
    }
}
