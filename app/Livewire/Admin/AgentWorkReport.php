<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\TicketMessageType;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

final class AgentWorkReport extends Component
{
    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->role === UserRole::Admin, 403);
    }

    /**
     * @return Collection<int, array{agent: User, tickets_assigned: int, replies_sent: int, internal_notes: int, status_changes: int, resolved_tickets: int, ai_runs_initiated: int}>
     */
    public function getAgentMetrics(): Collection
    {
        $agents = User::query()
            ->whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
            ->withCount([
                'assignedTickets as tickets_assigned',
                'ticketMessages as replies_sent' => function (mixed $q): void {
                    /** @var Builder<TicketMessage> $q */
                    $q->where('type', TicketMessageType::Public);
                },
                'ticketMessages as internal_notes' => function (mixed $q): void {
                    /** @var Builder<TicketMessage> $q */
                    $q->where('type', TicketMessageType::Internal);
                },
                'assignedTickets as resolved_tickets' => function (mixed $q): void {
                    /** @var Builder<Ticket> $q */
                    $q->where('status', TicketStatus::Resolved);
                },
                'aiRuns as ai_runs_initiated',
            ])
            ->orderBy('name')
            ->get();

        /** @var array<string, int> $statusChangeCounts */
        $statusChangeCounts = DB::table('audit_logs')
            ->select('actor_user_id', DB::raw('count(*) as cnt'))
            ->where('action', 'status_changed')
            ->whereIn('actor_user_id', $agents->pluck('id'))
            ->groupBy('actor_user_id')
            ->pluck('cnt', 'actor_user_id')
            ->all();

        return $agents->map(function (User $agent) use ($statusChangeCounts): array {
            /** @var int $ticketsAssigned */
            $ticketsAssigned = $agent->getAttribute('tickets_assigned');
            /** @var int $repliesSent */
            $repliesSent = $agent->getAttribute('replies_sent');
            /** @var int $internalNotes */
            $internalNotes = $agent->getAttribute('internal_notes');
            /** @var int $resolvedTickets */
            $resolvedTickets = $agent->getAttribute('resolved_tickets');
            /** @var int $aiRunsInitiated */
            $aiRunsInitiated = $agent->getAttribute('ai_runs_initiated');

            return [
                'agent' => $agent,
                'tickets_assigned' => $ticketsAssigned,
                'replies_sent' => $repliesSent,
                'internal_notes' => $internalNotes,
                'status_changes' => $statusChangeCounts[$agent->id] ?? 0,
                'resolved_tickets' => $resolvedTickets,
                'ai_runs_initiated' => $aiRunsInitiated,
            ];
        });
    }

    public function render(): View
    {
        return view('livewire.admin.agent-work-report', [
            'agentMetrics' => $this->getAgentMetrics(),
        ]);
    }
}
