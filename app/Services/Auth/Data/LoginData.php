<?php

namespace App\Services\Auth\Data;

use Spatie\LaravelData\Data;

class LoginData extends Data
{
    public function __construct(
        public string $provider,
        public ?string $email,
        public ?string $password,
        public ?string $oauth_token,
    ) {}

    public static function rules(): array
    {
        return [
            'provider' => ['required', 'string'],

            'email' => ['required_if:provider,local', 'email'],
            'password' => ['required_if:provider,local', 'string'],

            'oauth_token' => ['nullable', 'string'],
        ];
    }
}
