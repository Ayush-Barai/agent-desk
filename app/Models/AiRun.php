<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use Carbon\CarbonInterface;
use Database\Factories\AiRunFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string|null $ticket_id
 * @property-read string|null $initiated_by_user_id
 * @property-read AiRunType $run_type
 * @property-read AiRunStatus $status
 * @property-read string $input_hash
 * @property-read array<mixed>|null $input_json
 * @property-read array<mixed>|null $output_json
 * @property-read string|null $provider
 * @property-read string|null $model
 * @property-read string|null $error_message
 * @property-read string|null $progress_state
 * @property-read CarbonInterface|null $started_at
 * @property-read CarbonInterface|null $completed_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class AiRun extends Model
{
    /** @use HasFactory<AiRunFactory> */
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
            'initiated_by_user_id' => 'string',
            'run_type' => AiRunType::class,
            'status' => AiRunStatus::class,
            'input_hash' => 'string',
            'input_json' => 'array',
            'output_json' => 'array',
            'provider' => 'string',
            'model' => 'string',
            'error_message' => 'string',
            'progress_state' => 'string',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    /**
     * @return HasMany<TicketMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }
}
