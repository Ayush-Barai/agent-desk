<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketMessageType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
final class TicketMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'type' => TicketMessageType::Public,
            'body' => fake()->paragraph(),
            'is_ai_draft' => false,
        ];
    }

    public function internal(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => TicketMessageType::Internal,
        ]);
    }

    public function aiDraft(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_ai_draft' => true,
        ]);
    }
}
