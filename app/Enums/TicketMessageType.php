<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketMessageType: string
{
    case Public = 'public';
    case Internal = 'internal';

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
            self::Public => 'Public',
            self::Internal => 'Internal Note',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Public => 'bg-green-100 text-green-800',
            self::Internal => 'bg-yellow-100 text-yellow-800',
        };
    }
}
