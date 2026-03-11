<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case New = 'new';
    case Triaged = 'triaged';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Resolved = 'resolved';

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
            self::New => 'New',
            self::Triaged => 'Triaged',
            self::InProgress => 'In Progress',
            self::Waiting => 'Waiting',
            self::Resolved => 'Resolved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'bg-blue-100 text-blue-800',
            self::Triaged => 'bg-yellow-100 text-yellow-800',
            self::InProgress => 'bg-indigo-100 text-indigo-800',
            self::Waiting => 'bg-orange-100 text-orange-800',
            self::Resolved => 'bg-green-100 text-green-800',
        };
    }
}
