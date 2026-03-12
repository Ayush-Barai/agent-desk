<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\TicketMessageType;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\AiRun;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
            ->orderBy('name')
            ->get();

        return $agents->map(fn (User $agent): array => [
            'agent' => $agent,
            'tickets_assigned' => Ticket::query()
                ->where('assigned_to_user_id', $agent->id)
                ->count(),
            'replies_sent' => TicketMessage::query()
                ->where('user_id', $agent->id)
                ->where('type', TicketMessageType::Public)
                ->count(),
            'internal_notes' => TicketMessage::query()
                ->where('user_id', $agent->id)
                ->where('type', TicketMessageType::Internal)
                ->count(),
            'status_changes' => AuditLog::query()
                ->where('actor_user_id', $agent->id)
                ->where('action', 'status_changed')
                ->count(),
            'resolved_tickets' => Ticket::query()
                ->where('assigned_to_user_id', $agent->id)
                ->where('status', TicketStatus::Resolved)
                ->count(),
            'ai_runs_initiated' => AiRun::query()
                ->where('initiated_by_user_id', $agent->id)
                ->count(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.admin.agent-work-report', [
            'agentMetrics' => $this->getAgentMetrics(),
        ]);
    }
}
