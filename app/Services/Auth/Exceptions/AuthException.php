<?php

namespace App\Services\Auth\Exceptions;

use RuntimeException;

class AuthException extends RuntimeException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials');
    }

    public static function unsupportedProvider(string $provider): self
    {
        return new self("Unsupported auth provider: {$provider}");
    }
}
