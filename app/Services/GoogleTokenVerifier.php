<?php

namespace App\Services;

use App\Data\GoogleUserData;
use App\Exceptions\InvalidGoogleTokenException;
use Google\Client as GoogleClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class GoogleTokenVerifier
{
    public function __construct(
        private readonly GoogleClient $googleClient = new GoogleClient(),
    ) {
    }

    public function verify(string $idToken): GoogleUserData
    {
        $payload = $this->googleClient->verifyIdToken($idToken);

        if (! is_array($payload)) {
            throw new InvalidGoogleTokenException('The Google ID token is invalid.');
        }

        $audience = Arr::get($payload, 'aud');
        $allowedClientIds = config('services.google.client_ids', []);

        if ($allowedClientIds !== [] && ! in_array($audience, $allowedClientIds, true)) {
            throw new InvalidGoogleTokenException('The Google ID token audience is invalid.');
        }

        $email = Str::lower((string) Arr::get($payload, 'email'));
        $googleId = (string) Arr::get($payload, 'sub');
        $name = trim((string) (Arr::get($payload, 'name') ?: Arr::get($payload, 'given_name') ?: Str::before($email, '@')));
        $emailVerified = filter_var(Arr::get($payload, 'email_verified'), FILTER_VALIDATE_BOOL);

        if ($email === '' || $googleId === '' || ! $emailVerified) {
            throw new InvalidGoogleTokenException('The Google account must provide a verified email address.');
        }

        return new GoogleUserData(
            googleId: $googleId,
            email: $email,
            name: $name !== '' ? $name : 'Google User',
            emailVerified: true,
        );
    }
}
