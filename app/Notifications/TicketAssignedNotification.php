<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Ticket $ticket,
        private readonly User $assignedBy,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'assigned_by_name' => $this->assignedBy->name,
            'message' => sprintf('Ticket "%s" has been assigned to you by %s.', $this->ticket->subject, $this->assignedBy->name),
        ];
    }
}
