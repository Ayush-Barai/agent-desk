<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class TriageQueue extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Ticket>
     */
    public function getTickets(): LengthAwarePaginator
    {
        $query = Ticket::query()
            ->where('status', TicketStatus::New)
            ->whereNull('assigned_to_user_id')
            ->with(['requester', 'category'])
            ->latest('created_at');

        if ($this->search !== '') {
            $query->where('subject', 'like', '%'.$this->search.'%');
        }

        return $query->paginate(15);
    }

    public function deleteTicket(Ticket $ticket): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->isAdmin(), 403);

        $ticket->delete();

        $this->dispatch('ticket-deleted');
    }

    public function render(): View
    {
        return view('livewire.agent.triage-queue', [
            'tickets' => $this->getTickets(),
        ]);
    }
}
