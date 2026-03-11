<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class AgentTicketList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public string $scope = 'mine';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function updatedScope(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Ticket>
     */
    public function getTickets(): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Ticket::query()
            ->with(['requester', 'category', 'assignee'])
            ->latest('updated_at');

        if ($this->scope === 'mine') {
            $query->where('assigned_to_user_id', $user->id);
        }

        if ($this->search !== '') {
            $query->where('subject', 'like', '%'.$this->search.'%');
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->priority !== '') {
            $query->where('priority', $this->priority);
        }

        return $query->paginate(15);
    }

    /**
     * @return list<TicketStatus>
     */
    public function getStatuses(): array
    {
        return TicketStatus::cases();
    }

    /**
     * @return list<TicketPriority>
     */
    public function getPriorities(): array
    {
        return TicketPriority::cases();
    }

    public function render(): View
    {
        return view('livewire.agent.agent-ticket-list', [
            'tickets' => $this->getTickets(),
            'statuses' => $this->getStatuses(),
            'priorities' => $this->getPriorities(),
        ]);
    }
}
