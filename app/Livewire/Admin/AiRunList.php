<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

final class AiRunList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, AiRun>
     */
    public function getRuns(): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->can('viewAny', AiRun::class), 403);

        $query = AiRun::query()
            ->with(['ticket', 'initiator'])
            ->latest('created_at');

        if ($this->search !== '') {
            $query->whereHas('ticket', function (Builder $q): void {
                $q->where('subject', 'like', sprintf('%%%s%%', $this->search));
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter !== '') {
            $query->where('run_type', $this->typeFilter);
        }

        return $query->paginate(20);
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
        return view('livewire.admin.ai-run-list', [
            'runs' => $this->getRuns(),
        ]);
    }
}
