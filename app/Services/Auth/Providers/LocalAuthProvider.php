<?php

namespace App\Services\Auth\Providers;

use App\Models\User;
use App\Services\Auth\Contracts\AuthProviderContract;
use App\Services\Auth\Data\AuthResultData;
use App\Services\Auth\Data\LoginData;
use App\Services\Auth\Data\RegisterData;
use App\Services\Auth\Data\UserData;
use App\Services\Auth\Exceptions\AuthException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LocalAuthProvider implements AuthProviderContract
{
    public function key(): string
    {
        return 'local';
    }

    public function register(RegisterData $data): AuthResultData
    {
        return DB::transaction(function () use ($data) { // if mail event doesn't work - not register
            $user = User::create([
                'name' => (string)$data->name,
                'email' => (string)$data->email,
                'password' => bcrypt((string)$data->password),
            ]);

            $user->assignRole((string)$data->role);

            // send verification email
            $user->sendEmailVerificationNotification();

            $token = $user->createToken('spa')->plainTextToken;

            return new AuthResultData(
                token: $token,
                user: UserData::fromModel($user),
                must_verify_email: true,
                email_verified: $user->hasVerifiedEmail(),
            );
        });
    }

    public function login(LoginData $data): AuthResultData
    {
        $user = User::query()->where('email', (string) $data->email)->first();

        if (!$user || !Hash::check((string) $data->password, $user->password)) {
            throw AuthException::invalidCredentials();
        }

        $token = $user->createToken('spa')->plainTextToken;

        return new AuthResultData(
            token: $token,
            user: UserData::fromModel($user),
            must_verify_email: true,
            email_verified: $user->hasVerifiedEmail(),
        );
    }
}
