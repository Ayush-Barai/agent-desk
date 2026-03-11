<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
final class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticket = Ticket::factory()->create();

        return [
            'actor_user_id' => User::factory(),
            'ticket_id' => $ticket->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
            'action' => 'status_changed',
            'old_values_json' => null,
            'new_values_json' => null,
            'meta_json' => null,
        ];
    }
}
