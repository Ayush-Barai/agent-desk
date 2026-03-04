<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TicketAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'ticket_id' => 'string',
            'original_name' => 'string',
            'path' => 'string',
            'mime_type' => 'string',
            'size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* --------------------------
     | Relationships
     --------------------------*/

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}