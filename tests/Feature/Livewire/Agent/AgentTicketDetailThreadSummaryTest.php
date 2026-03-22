<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Agent;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\UserRole;
use App\Livewire\Agent\AgentTicketDetail;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AgentTicketDetailThreadSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_thread_summary_creates_ai_run(): void
    {
        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $ticket = Ticket::factory()
            ->has(\App\Models\TicketMessage::factory(3))
            ->create();

        $this->actingAs($agent);

        $component = $this->livewire(AgentTicketDetail::class, ['ticket' => $ticket]);

        $component->call('runThreadSummary');

        $aiRun = AiRun::query()
            ->where('ticket_id', $ticket->id)
            ->where('run_type', AiRunType::ThreadSummary)
            ->first();

        expect($aiRun)->not->toBeNull();
        expect($aiRun->status)->toBe(AiRunStatus::Queued);
        expect($aiRun->initiated_by_user_id)->toBe($agent->id);
    }

    public function test_thread_summary_caches_duplicate_requests(): void
    {
        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $ticket = Ticket::factory()
            ->has(\App\Models\TicketMessage::factory(2))
            ->create();

        $this->actingAs($agent);

        $component = $this->livewire(AgentTicketDetail::class, ['ticket' => $ticket]);

        // First run
        $component->call('runThreadSummary');

        $firstRun = AiRun::query()
            ->where('ticket_id', $ticket->id)
            ->where('run_type', AiRunType::ThreadSummary)
            ->first();

        // Attempt second run with same input
        $component->call('runThreadSummary');

        $allRuns = AiRun::query()
            ->where('ticket_id', $ticket->id)
            ->where('run_type', AiRunType::ThreadSummary)
            ->get();

        // Should show info message instead of creating duplicate
        expect($allRuns)->toHaveCount(1);
    }

    public function test_insert_summary_as_note(): void
    {
        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'output_json' => [
                'thread_summary' => 'This is a test summary',
                'recommended_next_action' => 'Follow up with customer',
            ],
        ]);

        $this->actingAs($agent);

        $component = $this->livewire(AgentTicketDetail::class, ['ticket' => $ticket]);

        $component->call('insertSummaryAsNote', $aiRun->id);

        // Component should now have the summary in the reply body as internal note
        expect($component->get('replyBody'))->toContain('This is a test summary');
        expect($component->get('replyType'))->toBe('internal');
    }

    public function test_get_latest_thread_summary_run(): void
    {
        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $ticket = Ticket::factory()->create();

        AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'created_at' => now()->subMinutes(10),
        ]);

        $latestRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'created_at' => now(),
        ]);

        $this->actingAs($agent);

        $component = $this->livewire(AgentTicketDetail::class, ['ticket' => $ticket]);

        $run = $component->call('getLatestThreadSummaryRun');

        expect($run)->not->toBeNull();
    }
}
