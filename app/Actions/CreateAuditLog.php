<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class CreateAuditLog
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @param  array<string, mixed>|null  $meta
     */
    public function execute(
        string $action,
        ?User $actor = null,
        ?string $ticketId = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $meta = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_user_id' => $actor?->id,
            'ticket_id' => $ticketId,
            'auditable_type' => $auditable instanceof Model ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'old_values_json' => $oldValues,
            'new_values_json' => $newValues,
            'meta_json' => $meta,
        ]);
    }
}
