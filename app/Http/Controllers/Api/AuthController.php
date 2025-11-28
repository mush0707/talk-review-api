<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\Contracts\AuthServiceContract;
use App\Services\Auth\Data\LoginData;
use App\Services\Auth\Data\RegisterData;
use App\Services\Auth\Exceptions\AuthException;
use App\Services\Users\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseApiController
{
    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function register(RegisterData $data, AuthServiceContract $auth): JsonResponse
    {
        try {
            $result = $auth->register($data);
            return $this->success($result);
        } catch (AuthException $e) {
            return $this->fail($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) return $this->unauthorized();

        if ($user->hasVerifiedEmail()) {
            return $this->fail('Email already verified', Response::HTTP_BAD_REQUEST);
        }

        $user->sendEmailVerificationNotification();
        return $this->success(['ok' => true]);
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = $this->userRepository->getById($id);

        // Same check Laravel uses: hash is sha1(email_for_verification)
        $expected = sha1($user->getEmailForVerification());

        if (! hash_equals($expected, (string) $hash)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        return redirect()->to($frontend . '/email-verified?ok=1');
    }

    public function login(LoginData $data, AuthServiceContract $auth): JsonResponse
    {
        try {
            $result = $auth->login($data);
            return $this->success($result);
        } catch (AuthException $e) {
            return $this->fail($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function me(AuthServiceContract $auth): JsonResponse
    {
        $user = request()->user();
        if (!$user) {
            return $this->unauthorized();
        }

        return $this->success([
            'user' => $auth->me($user),
            'must_verify_email' => true,
            'email_verified' => $user->hasVerifiedEmail(),
        ]);
    }

    public function logout(AuthServiceContract $auth): JsonResponse
    {
        $user = request()->user();
        if (!$user) {
            return $this->unauthorized();
        }

        $auth->logout($user);

        return $this->success(['ok' => true]);
    }
}
