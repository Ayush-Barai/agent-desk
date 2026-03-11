<?php

declare(strict_types=1);

namespace App\Enums;

enum AiRunStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Running => 'Running',
            self::Succeeded => 'Succeeded',
            self::Failed => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Queued => 'bg-gray-100 text-gray-800',
            self::Running => 'bg-blue-100 text-blue-800',
            self::Succeeded => 'bg-green-100 text-green-800',
            self::Failed => 'bg-red-100 text-red-800',
        };
    }
}
