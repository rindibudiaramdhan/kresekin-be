<?php

namespace App\Data;

final class GoogleUserData
{
    public function __construct(
        public readonly string $googleId,
        public readonly string $email,
        public readonly string $name,
        public readonly bool $emailVerified,
    ) {
    }
}
