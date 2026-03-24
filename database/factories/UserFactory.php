<?php

namespace Database\Factories;

use App\Enums\AuthProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'auth_provider' => AuthProvider::LOCAL,
            'google_id' => null,
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'auth_provider' => AuthProvider::GOOGLE,
            'google_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'password' => null,
        ]);
    }
}
