<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Agent;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\UserRole;
use App\Livewire\Agent\AgentTicketDetail;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
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

    public function test_livewire_can_trigger_thread_summary(): void
    {
        Bus::fake();

        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();
        TicketMessage::factory(2)->create(['ticket_id' => $ticket->id]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->call('runThreadSummary');

        $this->assertDatabaseHas('ai_runs', [
            'ticket_id' => $ticket->id,
            'initiated_by_user_id' => $agent->id,
            'run_type' => AiRunType::ThreadSummary->value,
            'status' => AiRunStatus::Queued->value,
        ]);
    }

    public function test_livewire_insert_summary_as_note(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'output_json' => [
                'thread_summary' => 'Customer needs refund',
                'recommended_next_action' => 'Process refund',
            ],
        ]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->call('insertSummaryAsNote', $aiRun->id)
            ->assertSet('replyType', 'internal')
            ->assertSet('replyBody', fn (string $body): bool => str_contains($body, 'Customer needs refund'));
    }

    public function test_livewire_get_latest_thread_summary_run(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();

        $run1 = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'created_at' => now()->subMinutes(5),
        ]);

        $run2 = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'created_at' => now(),
        ]);

        $component = Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket]);

        $latestRun = $component->instance()->getLatestThreadSummaryRun();

        expect($latestRun?->id)->toBe($run2->id);
    }

    public function test_livewire_insert_summary_appends_to_existing_internal_note(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'output_json' => [
                'thread_summary' => 'Customer issue summary',
                'recommended_next_action' => null,
            ],
        ]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->set('replyBody', 'Existing note')
            ->set('replyType', 'internal')
            ->call('insertSummaryAsNote', $aiRun->id)
            ->assertSet('replyBody', fn (string $body): bool => str_contains($body, 'Existing note') && str_contains($body, 'Customer issue summary'));
    }

    public function test_livewire_insert_summary_with_null_output(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'output_json' => null,
        ]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->call('insertSummaryAsNote', $aiRun->id)
            ->assertSet('replyBody', '');
    }

    public function test_livewire_thread_summary_duplicate_request(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();
        TicketMessage::factory(2)->create(['ticket_id' => $ticket->id]);

        // Create an in-flight AI run with the same input hash
        $inputHash = hash('sha256', json_encode([
            'ticket_id' => $ticket->id,
            'subject' => $ticket->subject,
            'message_count' => 2,
        ], JSON_THROW_ON_ERROR));

        $inFlightRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
            'input_hash' => $inputHash,
        ]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->call('runThreadSummary')
            ->assertDispatched('info');
    }

    public function test_livewire_thread_summary_uses_cached_result(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();
        TicketMessage::factory(2)->create(['ticket_id' => $ticket->id]);

        // Create a cached successful AI run with the same input hash
        $inputHash = hash('sha256', json_encode([
            'ticket_id' => $ticket->id,
            'subject' => $ticket->subject,
            'message_count' => 2,
        ], JSON_THROW_ON_ERROR));

        $cachedRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Succeeded,
            'input_hash' => $inputHash,
        ]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->call('runThreadSummary')
            ->assertDispatched('info');
    }
}

