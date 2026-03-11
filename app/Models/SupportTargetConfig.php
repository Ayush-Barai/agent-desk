<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SupportTargetConfigFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read string $id
 * @property-read int $first_response_hours
 * @property-read int $resolution_hours
 */
final class SupportTargetConfig extends Model
{
    /** @use HasFactory<SupportTargetConfigFactory> */
    use HasFactory;

    use HasUuids;

    #[Override]
    protected $table = 'support_target_configs';

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'first_response_hours' => 'integer',
            'resolution_hours' => 'integer',
        ];
    }
}
