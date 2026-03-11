<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
final class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => TicketStatus::New,
            'priority' => null,
            'escalation_required' => false,
        ];
    }

    public function triaged(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TicketStatus::Triaged,
            'priority' => TicketPriority::Medium,
            'triaged_at' => now(),
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TicketStatus::InProgress,
            'priority' => TicketPriority::Medium,
            'triaged_at' => now()->subHour(),
        ]);
    }

    public function resolved(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TicketStatus::Resolved,
            'priority' => TicketPriority::Medium,
            'resolved_at' => now(),
        ]);
    }

    public function urgent(): self
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => TicketPriority::Urgent,
            'escalation_required' => true,
        ]);
    }

    public function assignedTo(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'assigned_to_user_id' => $user->id,
        ]);
    }
}
