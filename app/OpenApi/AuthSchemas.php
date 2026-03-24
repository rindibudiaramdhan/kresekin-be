<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['username', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'username', type: 'string', example: 'rindi_dev'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'rindi@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Password123'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['username', 'password'],
    properties: [
        new OA\Property(property: 'username', type: 'string', example: 'rindi_dev'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Password123'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'GoogleRegisterRequest',
    required: ['id_token'],
    properties: [
        new OA\Property(property: 'id_token', type: 'string', example: 'eyJhbGciOiJSUzI1NiIsImtpZCI6Ij...'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'User',
    required: ['id', 'username', 'email', 'auth_provider', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'username', type: 'string', example: 'rindi_dev'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'rindi@example.com'),
        new OA\Property(property: 'auth_provider', type: 'string', enum: ['local', 'google'], example: 'local'),
        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'AuthSuccessResponse',
    required: ['access_token', 'token_type', 'expires_in', 'user'],
    properties: [
        new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The provided credentials are incorrect.'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The username field is required.'),
        new OA\Property(property: 'errors', type: 'object', example: ['username' => ['The username field is required.']]),
    ],
    type: 'object',
)]
class AuthSchemas
{
}
