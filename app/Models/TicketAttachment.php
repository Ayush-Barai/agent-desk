<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\TicketAttachmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $ticket_id
 * @property-read string|null $ticket_message_id
 * @property-read string $uploaded_by_user_id
 * @property-read string $storage_path
 * @property-read string $disk
 * @property-read string $original_name
 * @property-read string $mime_type
 * @property-read int $size_bytes
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class TicketAttachment extends Model
{
    /** @use HasFactory<TicketAttachmentFactory> */
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
            'ticket_message_id' => 'string',
            'uploaded_by_user_id' => 'string',
            'storage_path' => 'string',
            'disk' => 'string',
            'original_name' => 'string',
            'mime_type' => 'string',
            'size_bytes' => 'integer',
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
     * @return BelongsTo<TicketMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
