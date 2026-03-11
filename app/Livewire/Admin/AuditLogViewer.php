<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

final class AuditLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $actionFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActionFilter(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function getLogs(): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->can('viewAny', AuditLog::class), 403);

        $query = AuditLog::query()
            ->with(['actor', 'ticket'])
            ->latest('created_at');

        if ($this->search !== '') {
            $query->where('action', 'like', sprintf('%%%s%%', $this->search));
        }

        if ($this->actionFilter !== '') {
            $query->where('action', $this->actionFilter);
        }

        return $query->paginate(20);
    }

    /**
     * @return list<string>
     */
    public function getActionTypes(): array
    {
        /** @var list<string> $actions */
        $actions = AuditLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();

        return $actions;
    }

    public function render(): View
    {
        return view('livewire.admin.audit-log-viewer', [
            'logs' => $this->getLogs(),
            'actionTypes' => $this->getActionTypes(),
        ]);
    }
}
