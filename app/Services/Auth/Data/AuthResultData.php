<?php

namespace App\Services\Auth\Data;

use Spatie\LaravelData\Data;

class AuthResultData extends Data
{
    public function __construct(
        public string $token,
        public UserData $user,
        public bool $must_verify_email,
        public bool $email_verified,
    ) {}
}
