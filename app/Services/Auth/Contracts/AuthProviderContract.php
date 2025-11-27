<?php

namespace App\Services\Auth\Contracts;

use App\Services\Auth\Data\AuthResultData;
use App\Services\Auth\Data\LoginData;
use App\Services\Auth\Data\RegisterData;

interface AuthProviderContract
{
    public function key(): string;

    public function register(RegisterData $data): AuthResultData;

    public function login(LoginData $data): AuthResultData;
}
