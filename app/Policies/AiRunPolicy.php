<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AiRun;
use App\Models\User;

final class AiRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, AiRun $aiRun): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }
}
