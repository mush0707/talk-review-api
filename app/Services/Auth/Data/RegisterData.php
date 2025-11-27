<?php

namespace App\Services\Auth\Data;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public string $provider, // local now, oauth later
        public ?string $name,
        public ?string $email,
        public ?string $password,
        public ?string $password_confirmation,
        public ?string $role,
        // public ?string $oauth_token, // reserved for future oauth providers
    ) {}

    public static function rules(): array
    {
        return [
            'provider' => ['required', 'string'],

            // local registration
            'name' => ['required_if:provider,local', 'string', 'max:255'],
            'email' => ['required_if:provider,local', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required_if:provider,local', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required_if:provider,local', 'string'],

            'role' => [
                'required_if:provider,local',
                'string',
                Rule::exists('roles', 'name'),
            ],

            // future oauth
            // 'oauth_token' => ['nullable', 'string'],
        ];
    }
}
