<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Carbon\CarbonInterface;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $requester_id
 * @property-read string|null $assigned_to_user_id
 * @property-read string|null $category_id
 * @property-read string $subject
 * @property-read string $description
 * @property-read string|null $summary
 * @property-read TicketStatus $status
 * @property-read TicketPriority|null $priority
 * @property-read bool $escalation_required
 * @property-read CarbonInterface|null $first_response_due_at
 * @property-read CarbonInterface|null $resolution_due_at
 * @property-read CarbonInterface|null $first_responded_at
 * @property-read CarbonInterface|null $resolved_at
 * @property-read CarbonInterface|null $triaged_at
 * @property-read CarbonInterface|null $last_requester_message_at
 * @property-read CarbonInterface|null $last_agent_message_at
 * @property-read CarbonInterface|null $overdue_response_notified_at
 * @property-read CarbonInterface|null $overdue_resolution_notified_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'requester_id' => 'string',
            'assigned_to_user_id' => 'string',
            'category_id' => 'string',
            'subject' => 'string',
            'description' => 'string',
            'summary' => 'string',
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'escalation_required' => 'boolean',
            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'triaged_at' => 'datetime',
            'last_requester_message_at' => 'datetime',
            'last_agent_message_at' => 'datetime',
            'overdue_response_notified_at' => 'datetime',
            'overdue_resolution_notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<TicketMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    /**
     * @return HasMany<TicketAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /**
     * @return HasMany<AiRun, $this>
     */
    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class);
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tag');
    }
}
