<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\OverdueResolutionNotification;
use App\Notifications\OverdueResponseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class CheckOverdueTargetsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $this->checkOverdueResponses();
        $this->checkOverdueResolutions();
    }

    private function checkOverdueResponses(): void
    {
        $tickets = Ticket::query()
            ->whereNotNull('first_response_due_at')
            ->where('first_response_due_at', '<', now())
            ->whereNull('first_responded_at')
            ->whereNull('overdue_response_notified_at')
            ->whereNot('status', TicketStatus::Resolved)
            ->with(['assignee'])
            ->get();

        foreach ($tickets as $ticket) {
            $this->notifyForOverdueResponse($ticket);
        }
    }

    private function checkOverdueResolutions(): void
    {
        $tickets = Ticket::query()
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<', now())
            ->whereNull('resolved_at')
            ->whereNull('overdue_resolution_notified_at')
            ->whereNot('status', TicketStatus::Resolved)
            ->with(['assignee'])
            ->get();

        foreach ($tickets as $ticket) {
            $this->notifyForOverdueResolution($ticket);
        }
    }

    private function notifyForOverdueResponse(Ticket $ticket): void
    {
        $this->getNotifiableUsers($ticket)->each(
            function (User $user) use ($ticket): void {
                $user->notify(new OverdueResponseNotification($ticket));
            }
        );

        $ticket->update(['overdue_response_notified_at' => now()]);
    }

    private function notifyForOverdueResolution(Ticket $ticket): void
    {
        $this->getNotifiableUsers($ticket)->each(
            function (User $user) use ($ticket): void {
                $user->notify(new OverdueResolutionNotification($ticket));
            }
        );

        $ticket->update(['overdue_resolution_notified_at' => now()]);
    }

    /**
     * @return Collection<int, User>
     */
    private function getNotifiableUsers(Ticket $ticket): Collection
    {
        $assignee = $ticket->assignee;

        if ($assignee instanceof User) {
            return new Collection([$assignee]);
        }

        return User::query()
            ->where('role', 'admin')
            ->get();
    }
}
