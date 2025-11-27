<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth', description: 'Authentication endpoints')]
final class AuthPaths
{
    #[OA\Post(
        path: '/api/auth/register',
        operationId: 'authRegister',
        summary: 'Register a user (provider-based). Currently provider=local.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthRegisterRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Registered',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function register(): void {}

    #[OA\Post(
        path: '/api/auth/email/verification-notification',
        operationId: 'authResendVerification',
        summary: 'Resend verification email (requires auth)',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sent',
                content: new OA\JsonContent(ref: '#/components/schemas/GenericOkResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Already verified / validation error'),
        ]
    )]
    public function resendVerification(): void {}

    #[OA\Get(
        path: '/api/auth/verify-email/{id}/{hash}',
        operationId: 'authVerifyEmail',
        summary: 'Verify email by signed link (redirects to frontend)',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'hash', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to frontend'),
            new OA\Response(response: 403, description: 'Invalid/expired signature'),
        ]
    )]
    public function verifyEmail(): void {}

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'authLogin',
        summary: 'Login (provider-based). Currently provider=local.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthLoginRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged in',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenResponse')
            ),
            new OA\Response(response: 422, description: 'Invalid credentials / validation error')
        ]
    )]
    public function login(): void {}

    #[OA\Get(
        path: '/api/auth/me',
        operationId: 'authMe',
        summary: 'Get current user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthMeResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function me(): void {}

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'authLogout',
        summary: 'Logout (revoke current token)',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out',
                content: new OA\JsonContent(ref: '#/components/schemas/GenericOkResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function logout(): void {}
}
