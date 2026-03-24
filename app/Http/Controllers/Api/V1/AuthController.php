<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AuthProvider;
use App\Exceptions\InvalidGoogleTokenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleRegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\GoogleTokenVerifier;
use App\Services\UsernameGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly GoogleTokenVerifier $googleTokenVerifier,
        private readonly UsernameGenerator $usernameGenerator,
    ) {
    }

    #[OA\Post(
        path: '/api/v1/auth/register',
        operationId: 'registerUser',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')),
        responses: [
            new OA\Response(response: 201, description: 'User registered successfully.', content: new OA\JsonContent(ref: '#/components/schemas/AuthSuccessResponse')),
            new OA\Response(response: 422, description: 'Validation error.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
        ],
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'username' => $request->string('username')->lower()->value(),
            'email' => strtolower($request->string('email')->value()),
            'password' => $request->string('password')->value(),
            'auth_provider' => AuthProvider::LOCAL,
        ]);

        return $this->respondWithToken(auth('api')->login($user), $user, Response::HTTP_CREATED);
    }

    #[OA\Post(
        path: '/api/v1/auth/login',
        operationId: 'loginUser',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
        responses: [
            new OA\Response(response: 200, description: 'User logged in successfully.', content: new OA\JsonContent(ref: '#/components/schemas/AuthSuccessResponse')),
            new OA\Response(response: 401, description: 'Invalid credentials.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('username', $request->string('username')->lower()->value())
            ->first();

        if (
            ! $user
            || $user->auth_provider !== AuthProvider::LOCAL
            || ! $user->password
            || ! Hash::check($request->string('password')->value(), $user->password)
        ) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken(auth('api')->login($user), $user);
    }

    #[OA\Post(
        path: '/api/v1/auth/google/register',
        operationId: 'registerOrLoginWithGoogle',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/GoogleRegisterRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Google user authenticated successfully.', content: new OA\JsonContent(ref: '#/components/schemas/AuthSuccessResponse')),
            new OA\Response(response: 401, description: 'Invalid Google token.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 409, description: 'Existing account conflict.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
        ],
    )]
    public function googleRegister(GoogleRegisterRequest $request): JsonResponse
    {
        try {
            $googleUser = $this->googleTokenVerifier->verify($request->string('id_token')->value());
        } catch (InvalidGoogleTokenException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::query()
            ->where('google_id', $googleUser->googleId)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user && $user->auth_provider === AuthProvider::LOCAL && $user->google_id === null) {
            return response()->json([
                'message' => 'An account with this email already exists and must use local authentication.',
            ], Response::HTTP_CONFLICT);
        }

        if (! $user) {
            $user = User::query()->create([
                'username' => $this->usernameGenerator->makeUnique($googleUser->name ?: $googleUser->email),
                'email' => $googleUser->email,
                'password' => null,
                'auth_provider' => AuthProvider::GOOGLE,
                'google_id' => $googleUser->googleId,
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'email' => $googleUser->email,
                'auth_provider' => AuthProvider::GOOGLE,
                'google_id' => $googleUser->googleId,
                'email_verified_at' => now(),
            ])->save();
        }

        return $this->respondWithToken(auth('api')->login($user), $user);
    }

    private function respondWithToken(string $token, User $user, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => UserResource::make($user),
        ], $status);
    }
}
