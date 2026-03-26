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

    /** @var array<int|string, string> */
    private static array $labelCache = [];

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

    public function getSummary(): string
    {
        $changes = [];
        $old = $this->old_values_json ?? [];
        $new = $this->new_values_json ?? [];

        // Common keys to humanize
        $keyMap = [
            'status' => 'Status',
            'priority' => 'Priority',
            'category_id' => 'Category',
            'assigned_to_user_id' => 'Assignee',
            'is_active' => 'Active',
            'name' => 'Name',
            'title' => 'Title',
            'body' => 'Body',
            'subject' => 'Subject',
        ];

        // Combine all unique keys from both old and new values
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($keys as $key) {
            $label = $keyMap[$key] ?? ucfirst(str_replace('_', ' ', $key));
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal === $newVal) {
                continue;
            }

            $readableOld = $this->formatValue($oldVal, $key);
            $readableNew = $this->formatValue($newVal, $key);

            if ($oldVal === null) {
                $changes[] = sprintf('%s set to "%s"', $label, $readableNew);
            } elseif ($newVal === null) {
                $changes[] = sprintf('%s cleared (was "%s")', $label, $readableOld);
            } else {
                $changes[] = sprintf('%s: %s → %s', $label, $readableOld, $readableNew);
            }
        }

        if ($changes === []) {
            return str_replace('_', ' ', ucfirst($this->action));
        }

        return implode(', ', $changes);
    }

    private function formatValue(mixed $value, string $key): string
    {
        if ($value === null || $value === '') {
            return 'none';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return 'data';
        }

        $id = is_scalar($value) ? (string) $value : 'data';

        // Try to resolve IDs into names for specific fields
        if (in_array($key, ['assigned_to_user_id', 'category_id', 'actor_user_id', 'requester_id'], true)) {
            if (isset(self::$labelCache[$id])) {
                return self::$labelCache[$id];
            }

            $name = null;
            if ($key === 'category_id') {
                $name = Category::query()->where('id', $id)->value('name');
            } else {
                // assume it's a user ID
                $name = User::query()->where('id', $id)->value('name');
            }

            if ($name !== null) {
                $resolvedName = is_scalar($name) ? (string) $name : 'unknown';

                return self::$labelCache[$id] = $resolvedName;
            }
        }

        return mb_strlen($id) > 30 ? sprintf('%s...', mb_substr($id, 0, 27)) : $id;
    }
}
