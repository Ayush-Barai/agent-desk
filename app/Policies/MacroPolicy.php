<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Macro;
use App\Models\User;

final class MacroPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Macro $macro): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Macro $macro): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Macro $macro): bool
    {
        return $user->isAdmin();
    }
}
