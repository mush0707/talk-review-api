<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    required: ['id', 'name', 'email', 'role'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Mush'),
        new OA\Property(property: 'email', type: 'string', example: 'mush@example.com'),
        new OA\Property(property: 'role', description: 'Role name from DB roles table', type: 'string', example: 'speaker'),
    ]
)]
#[OA\Schema(
    schema: 'AuthRegisterRequest',
    required: ['provider', 'name', 'email', 'password', 'password_confirmation', 'role'],
    properties: [
        new OA\Property(property: 'provider', type: 'string', example: 'local'),
        new OA\Property(property: 'name', type: 'string', example: 'Mush'),
        new OA\Property(property: 'email', type: 'string', example: 'mush@example.com'),
        new OA\Property(property: 'password', type: 'string', example: 'password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
        new OA\Property(property: 'role', description: 'Validated via exists:roles,name', type: 'string', example: 'speaker'),
        new OA\Property(property: 'oauth_token', description: 'Reserved for future OAuth providers', type: 'string', example: null, nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'AuthLoginRequest',
    required: ['provider', 'email', 'password'],
    properties: [
        new OA\Property(property: 'provider', type: 'string', example: 'local'),
        new OA\Property(property: 'email', type: 'string', example: 'mush@example.com'),
        new OA\Property(property: 'password', type: 'string', example: 'password123'),
        new OA\Property(property: 'oauth_token', description: 'Reserved for future OAuth providers', type: 'string', example: null, nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'AuthFlags',
    required: ['must_verify_email', 'email_verified'],
    properties: [
        new OA\Property(property: 'must_verify_email', type: 'boolean', example: true),
        new OA\Property(property: 'email_verified', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'AuthTokenResponse',
    required: ['token', 'user', 'must_verify_email', 'email_verified'],
    properties: [
        new OA\Property(property: 'token', type: 'string', example: '1|sanctum_token_here'),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'must_verify_email', type: 'boolean', example: true),
        new OA\Property(property: 'email_verified', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'AuthMeResponse',
    required: ['user', 'must_verify_email', 'email_verified'],
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'must_verify_email', type: 'boolean', example: true),
        new OA\Property(property: 'email_verified', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'GenericOkResponse',
    required: ['ok'],
    properties: [
        new OA\Property(property: 'ok', type: 'boolean', example: true),
    ]
)]
final class AuthSchemas {}
