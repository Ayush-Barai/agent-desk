<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;

final class TicketAttachmentPolicy
{
    public function view(User $user, TicketAttachment $ticketAttachment): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $ticketAttachment->ticket?->requester_id === $user->id;
    }

    public function create(User $user, Ticket $ticket): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $ticket->requester_id === $user->id;
    }

    public function delete(User $user, TicketAttachment $ticketAttachment): bool
    {
        return $user->isAdmin();
    }
}
