<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(openapi: '3.0.3')]
#[OA\Info(
    version: '1.0.0',
    title: 'Kresekin Auth API',
    description: 'REST authentication API built with Laravel, PostgreSQL, JWT, and Google Sign-In.',
)]
#[OA\Server(
    url: '/api/v1',
    description: 'Versioned API base path',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'Authentication endpoints',
)]
class OpenApiSpec
{
}
