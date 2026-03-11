<?php

declare(strict_types=1);

namespace App\Livewire\Requester;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class MyTicketsTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

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

    /**
     * @return LengthAwarePaginator<int, Ticket>
     */
    public function getTickets(): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Ticket::query()->where('requester_id', $user->id)
            ->with(['category', 'assignee'])
            ->latest('updated_at');

        if ($this->search !== '') {
            $query->where(function (Builder $q): void {
                $q->where('subject', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->priority !== '') {
            $query->where('priority', $this->priority);
        }

        return $query->paginate(10);
    }

    /**
     * @return array<int, TicketStatus>
     */
    public function getStatuses(): array
    {
        return TicketStatus::cases();
    }

    /**
     * @return array<int, TicketPriority>
     */
    public function getPriorities(): array
    {
        return TicketPriority::cases();
    }

    public function render(): View
    {
        return view('livewire.requester.my-tickets-table', [
            'tickets' => $this->getTickets(),
            'statuses' => $this->getStatuses(),
            'priorities' => $this->getPriorities(),
        ]);
    }
}
