<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGenerator
{
    public function makeUnique(string $seed): string
    {
        $base = Str::of($seed)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        $base = $base !== '' ? Str::substr($base, 0, 50) : 'user';
        $username = $base;
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $postfix = '_'.$suffix;
            $username = Str::substr($base, 0, 50 - strlen($postfix)).$postfix;
            $suffix++;
        }

        return $username;
    }
}
