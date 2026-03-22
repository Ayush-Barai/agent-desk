<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Agent;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\UserRole;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AgentTicketDetailThreadSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_thread_summary_ai_run_can_be_created(): void
    {
        $agent = User::factory()->create(['role' => UserRole::Agent]);
        $ticket = Ticket::factory()->create();

        // Create some messages for the ticket
        TicketMessage::factory(3)->create(['ticket_id' => $ticket->id]);

        $this->actingAs($agent);

        // Create an AI run for thread summary
        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
            'initiated_by_user_id' => $agent->id,
        ]);

        expect($aiRun)->not->toBeNull();
        expect($aiRun->run_type)->toBe(AiRunType::ThreadSummary);
        expect($aiRun->status)->toBe(AiRunStatus::Queued);
        expect($aiRun->initiated_by_user_id)->toBe($agent->id);
    }

    public function test_thread_summary_ai_run_with_output(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'output_json' => [
                'thread_summary' => 'This ticket is about a billing issue',
                'recommended_next_action' => 'Contact billing department',
            ],
        ]);

        expect($aiRun)->not->toBeNull();
        expect($aiRun->status)->toBe(AiRunStatus::Succeeded);
        expect($aiRun->output_json['thread_summary'])->toContain('billing');
        expect($aiRun->output_json['recommended_next_action'])->toBe('Contact billing department');
    }

    public function test_multiple_thread_summary_runs_for_same_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $run1 = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'created_at' => now()->subMinutes(10),
        ]);

        $run2 = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'created_at' => now(),
        ]);

        $allRuns = AiRun::query()
            ->where('ticket_id', $ticket->id)
            ->where('run_type', AiRunType::ThreadSummary)
            ->get();

        expect($allRuns)->toHaveCount(2);

        // Latest should be run2
        $latest = AiRun::query()
            ->where('ticket_id', $ticket->id)
            ->where('run_type', AiRunType::ThreadSummary)
            ->latest()
            ->first();

        expect($latest->id)->toBe($run2->id);
    }

    public function test_thread_summary_run_can_fail(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Failed,
            'error_message' => 'RATE_LIMIT: Too many requests',
        ]);

        expect($aiRun->status)->toBe(AiRunStatus::Failed);
        expect($aiRun->error_message)->toContain('RATE_LIMIT');
    }

    public function test_thread_summary_input_output_structure(): void
    {
        $ticket = Ticket::factory()->create();

        $inputJson = [
            'ticket_id' => $ticket->id,
            'subject' => 'Test Subject',
            'message_count' => 5,
        ];

        $outputJson = [
            'thread_summary' => 'This is a comprehensive summary',
            'recommended_next_action' => 'Follow up with user',
        ];

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'input_json' => $inputJson,
            'output_json' => $outputJson,
        ]);

        expect($aiRun->input_json['ticket_id'])->toBe($ticket->id);
        expect($aiRun->input_json['subject'])->toBe('Test Subject');
        expect($aiRun->input_json['message_count'])->toBe(5);
        expect($aiRun->output_json['thread_summary'])->not->toBeEmpty();
    }
}
