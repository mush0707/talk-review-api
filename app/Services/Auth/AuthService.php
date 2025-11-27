<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\Contracts\AuthProviderContract;
use App\Services\Auth\Contracts\AuthServiceContract;
use App\Services\Auth\Data\AuthResultData;
use App\Services\Auth\Data\LoginData;
use App\Services\Auth\Data\RegisterData;
use App\Services\Auth\Data\UserData;
use App\Services\Auth\Exceptions\AuthException;
use Illuminate\Support\Collection;

class AuthService implements AuthServiceContract
{
    /** @var Collection<string, AuthProviderContract> */
    private Collection $providers;

    public function __construct(iterable $providers)
    {
        $this->providers = collect($providers)
            ->mapWithKeys(fn (AuthProviderContract $p) => [$p->key() => $p]);
    }

    public function register(RegisterData $data): AuthResultData
    {
        return $this->provider($data->provider)->register($data);
    }

    public function login(LoginData $data): AuthResultData
    {
        return $this->provider($data->provider)->login($data);
    }

    public function me(User $user): UserData
    {
        return UserData::fromModel($user);
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    private function provider(string $key): AuthProviderContract
    {
        $provider = $this->providers->get($key);

        if (!$provider) {
            throw AuthException::unsupportedProvider($key);
        }

        return $provider;
    }
}
