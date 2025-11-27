<?php
namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Talk Review API'
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Local Docker'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    bearerFormat: 'Sanctum',
    scheme: 'bearer'
)]
final class Base {}
