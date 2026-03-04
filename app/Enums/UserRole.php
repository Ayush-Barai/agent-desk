<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Requester = 'requester';
    case Agent = 'agent';
    case Admin = 'admin';
}
