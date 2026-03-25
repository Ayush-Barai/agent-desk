<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->map(fn (mixed $v): string => is_scalar($v) ? (string) $v : '')
            ->values()
            ->all();

        return $actions;
    }

    public function downloadCsv(): StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->can('viewAny', AuditLog::class), 403);

        return response()->streamDownload(function (): void {
            $query = AuditLog::query()
                ->with(['actor', 'ticket'])
                ->latest('created_at');

            if ($this->search !== '') {
                $query->where('action', 'like', sprintf('%%%s%%', $this->search));
            }

            if ($this->actionFilter !== '') {
                $query->where('action', $this->actionFilter);
            }

            $file = fopen('php://output', 'w');

            if ($file !== false) {
                fputcsv($file, ['ID', 'Date', 'Actor', 'Action', 'Ticket ID', 'Old Values', 'New Values'], escape: '\\');

                $query->chunk(500, function (Collection $logs) use ($file): void {
                    /** @var Collection<int, AuditLog> $logs */
                    foreach ($logs as $log) {
                        fputcsv($file, [
                            $log->id,
                            $log->created_at->toDateTimeString(),
                            $log->actor ? $log->actor->name : 'System',
                            $log->action,
                            $log->ticket_id ?? 'N/A',
                            json_encode($log->old_values_json),
                            json_encode($log->new_values_json),
                        ], escape: '\\');
                    }
                });

                fclose($file);
            }
        }, 'audit_logs.csv');
    }

    public function render(): View
    {
        return view('livewire.admin.audit-log-viewer', [
            'logs' => $this->getLogs(),
            'actionTypes' => $this->getActionTypes(),
        ]);
    }
}
