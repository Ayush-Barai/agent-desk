<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Macro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Macro>
 */
final class MacroFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
