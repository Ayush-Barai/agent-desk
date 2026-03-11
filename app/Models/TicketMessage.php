<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketMessageType;
use Carbon\CarbonInterface;
use Database\Factories\TicketMessageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $ticket_id
 * @property-read string $user_id
 * @property-read TicketMessageType $type
 * @property-read string $body
 * @property-read bool $is_ai_draft
 * @property-read string|null $ai_run_id
 * @property-read array<mixed>|null $meta_json
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class TicketMessage extends Model
{
    /** @use HasFactory<TicketMessageFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'ticket_id' => 'string',
            'user_id' => 'string',
            'type' => TicketMessageType::class,
            'body' => 'string',
            'is_ai_draft' => 'boolean',
            'ai_run_id' => 'string',
            'meta_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<AiRun, $this>
     */
    public function aiRun(): BelongsTo
    {
        return $this->belongsTo(AiRun::class);
    }

    /**
     * @return HasMany<TicketAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
