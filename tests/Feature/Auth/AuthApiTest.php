<?php

use App\Data\GoogleUserData;
use App\Enums\AuthProvider;
use App\Exceptions\InvalidGoogleTokenException;
use App\Models\User;
use App\Services\GoogleTokenVerifier;
use Illuminate\Support\Facades\Hash;

it('registers a user and returns a jwt token', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'username' => 'rindi_dev',
        'email' => 'rindi@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.username', 'rindi_dev')
        ->assertJsonPath('user.email', 'rindi@example.com')
        ->assertJsonPath('user.auth_provider', 'local');

    expect($response->json('access_token'))->not->toBeEmpty();
    expect(User::query()->where('username', 'rindi_dev')->exists())->toBeTrue();
    expect(Hash::check('Password123', User::query()->firstOrFail()->password))->toBeTrue();
});

it('validates register payloads', function (): void {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['username', 'email', 'password']);
});

it('rejects duplicate username and email on register', function (): void {
    User::factory()->create([
        'username' => 'rindi_dev',
        'email' => 'rindi@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/register', [
        'username' => 'rindi_dev',
        'email' => 'rindi@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['username', 'email']);
});

it('logs in a local user and returns a jwt token', function (): void {
    User::factory()->create([
        'username' => 'rindi_dev',
        'email' => 'rindi@example.com',
        'password' => Hash::make('Password123'),
        'auth_provider' => AuthProvider::LOCAL,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'rindi_dev',
        'password' => 'Password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.username', 'rindi_dev');

    expect($response->json('access_token'))->not->toBeEmpty();
});

it('rejects invalid login credentials', function (): void {
    User::factory()->create([
        'username' => 'rindi_dev',
        'password' => Hash::make('Password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'rindi_dev',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The provided credentials are incorrect.');
});

it('rejects password login for google only accounts', function (): void {
    User::factory()->google()->create([
        'username' => 'google_only',
        'email' => 'google@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'google_only',
        'password' => 'Password123',
    ]);

    $response->assertUnauthorized();
});

it('creates a google user and returns a jwt token', function (): void {
    $this->mock(GoogleTokenVerifier::class, function ($mock): void {
        $mock->shouldReceive('verify')
            ->once()
            ->with('valid-google-token')
            ->andReturn(new GoogleUserData(
                googleId: 'google-123',
                email: 'google@example.com',
                name: 'Google Person',
                emailVerified: true,
            ));
    });

    $response = $this->postJson('/api/v1/auth/google/register', [
        'id_token' => 'valid-google-token',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('user.email', 'google@example.com')
        ->assertJsonPath('user.auth_provider', 'google');

    $user = User::query()->where('email', 'google@example.com')->firstOrFail();

    expect($user->google_id)->toBe('google-123');
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->username)->toStartWith('google_person');
});

it('logs in an existing google user without creating a duplicate', function (): void {
    $user = User::factory()->google()->create([
        'username' => 'google_person',
        'email' => 'google@example.com',
        'google_id' => 'google-123',
    ]);

    $this->mock(GoogleTokenVerifier::class, function ($mock): void {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(new GoogleUserData(
                googleId: 'google-123',
                email: 'google@example.com',
                name: 'Google Person',
                emailVerified: true,
            ));
    });

    $response = $this->postJson('/api/v1/auth/google/register', [
        'id_token' => 'valid-google-token',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);

    expect(User::query()->where('email', 'google@example.com')->count())->toBe(1);
});

it('rejects invalid google tokens', function (): void {
    $this->mock(GoogleTokenVerifier::class, function ($mock): void {
        $mock->shouldReceive('verify')
            ->once()
            ->andThrow(new InvalidGoogleTokenException('The Google ID token is invalid.'));
    });

    $response = $this->postJson('/api/v1/auth/google/register', [
        'id_token' => 'invalid-token',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJsonPath('message', 'The Google ID token is invalid.');
});

it('returns a conflict when google auth hits a local account', function (): void {
    User::factory()->create([
        'username' => 'local_user',
        'email' => 'local@example.com',
        'auth_provider' => AuthProvider::LOCAL,
        'google_id' => null,
    ]);

    $this->mock(GoogleTokenVerifier::class, function ($mock): void {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(new GoogleUserData(
                googleId: 'google-456',
                email: 'local@example.com',
                name: 'Local User',
                emailVerified: true,
            ));
    });

    $response = $this->postJson('/api/v1/auth/google/register', [
        'id_token' => 'valid-google-token',
    ]);

    $response
        ->assertConflict()
        ->assertJsonPath('message', 'An account with this email already exists and must use local authentication.');
});

it('generates swagger documentation', function (): void {
    $this->artisan('l5-swagger:generate')
        ->assertExitCode(0);

    expect(file_exists(storage_path('api-docs/api-docs.json')))->toBeTrue();
});
