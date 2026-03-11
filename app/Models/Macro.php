<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MacroFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $title
 * @property-read string $body
 * @property-read bool $is_active
 */
final class Macro extends Model
{
    /** @use HasFactory<MacroFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'title' => 'string',
            'body' => 'string',
            'is_active' => 'boolean',
        ];
    }
}
