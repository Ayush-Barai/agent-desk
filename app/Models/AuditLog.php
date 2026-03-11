<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read string $id
 * @property-read string|null $actor_user_id
 * @property-read string|null $ticket_id
 * @property-read string|null $auditable_type
 * @property-read string|null $auditable_id
 * @property-read string $action
 * @property-read array<mixed>|null $old_values_json
 * @property-read array<mixed>|null $new_values_json
 * @property-read array<mixed>|null $meta_json
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'actor_user_id' => 'string',
            'ticket_id' => 'string',
            'auditable_type' => 'string',
            'auditable_id' => 'string',
            'action' => 'string',
            'old_values_json' => 'array',
            'new_values_json' => 'array',
            'meta_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
