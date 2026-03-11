<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportTargetConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTargetConfig>
 */
final class SupportTargetConfigFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_response_hours' => 24,
            'resolution_hours' => 72,
        ];
    }
}
