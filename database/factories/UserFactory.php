<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    private static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'role' => UserRole::Requester,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Admin,
        ]);
    }

    public function agent(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Agent,
        ]);
    }

    public function requester(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Requester,
        ]);
    }
}
