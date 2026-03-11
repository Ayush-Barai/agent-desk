<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class OverdueResolutionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Ticket $ticket,
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
            'message' => sprintf('Ticket "%s" is overdue for resolution.', $this->ticket->subject),
        ];
    }
}
