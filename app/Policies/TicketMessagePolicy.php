<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TicketMessageType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

final class TicketMessagePolicy
{
    public function view(User $user, TicketMessage $ticketMessage): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $ticketMessage->type === TicketMessageType::Public;
    }

    public function createPublic(User $user, Ticket $ticket): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $ticket->requester_id === $user->id;
    }

    public function createInternal(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }
}
