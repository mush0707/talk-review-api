<?php

namespace App\Services\Auth\Contracts;

use App\Models\User;
use App\Services\Auth\Data\AuthResultData;
use App\Services\Auth\Data\LoginData;
use App\Services\Auth\Data\RegisterData;
use App\Services\Auth\Data\UserData;

interface AuthServiceContract
{
    public function register(RegisterData $data): AuthResultData;

    public function login(LoginData $data): AuthResultData;

    public function me(User $user): UserData;

    public function logout(User $user): void;
}
