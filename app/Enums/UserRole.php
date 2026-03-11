<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Agent = 'agent';
    case Requester = 'requester';

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
            self::Admin => 'Admin',
            self::Agent => 'Support Agent',
            self::Requester => 'Requester',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'red',
            self::Agent => 'blue',
            self::Requester => 'green',
        };
    }
}
