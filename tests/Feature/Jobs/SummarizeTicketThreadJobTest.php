<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Ai\Agents\ThreadSummaryAgent;
use App\DTOs\ThreadSummaryInput;
use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Jobs\SummarizeTicketThreadJob;
use App\Models\AiRun;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SummarizeTicketThreadJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_ai_run_as_running(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
        ]);

        $fakeOutput = [
            'thread_summary' => 'Test summary',
            'recommended_next_action' => 'Follow up',
        ];

        ThreadSummaryAgent::fake([$fakeOutput]);

        $input = new ThreadSummaryInput(
            ticketId: $ticket->id,
            subject: 'Test',
            messageHistory: [['role' => 'requester', 'body' => 'Test message']],
        );

        $job = new SummarizeTicketThreadJob($aiRun->id, $input);
        $job->handle();

        $aiRun->refresh();

        expect($aiRun->status)->toBe(AiRunStatus::Succeeded)
            ->and($aiRun->output_json['thread_summary'])->toBe('Test summary')
            ->and($aiRun->output_json['recommended_next_action'])->toBe('Follow up')
            ->and($aiRun->completed_at)->not->toBeNull();
    }

    public function test_ai_run_structure_is_valid(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
        ]);

        expect($aiRun->run_type)->toBe(AiRunType::ThreadSummary);
        expect($aiRun->status)->toBe(AiRunStatus::Queued);
        expect($aiRun->ticket_id)->toBe($ticket->id);
    }

    public function test_thread_summary_input_dto(): void
    {
        $messageHistory = [
            ['role' => 'requester', 'body' => 'I need help with X'],
            ['role' => 'agent', 'body' => 'Sure, let me help with X'],
            ['role' => 'requester', 'body' => 'Thanks!'],
        ];

        $input = new ThreadSummaryInput(
            ticketId: 'ticket-123',
            subject: 'Help with X',
            messageHistory: $messageHistory,
        );

        expect($input->ticketId)->toBe('ticket-123');
        expect($input->subject)->toBe('Help with X');
        expect($input->messageHistory)->toHaveCount(3);
    }

    public function test_job_handles_failure_gracefully(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
        ]);

        ThreadSummaryAgent::fake([fn (): never => throw new \RuntimeException('API error')]);

        $input = new ThreadSummaryInput(
            ticketId: $ticket->id,
            subject: 'Test',
            messageHistory: [['role' => 'requester', 'body' => 'Test message']],
        );

        $job = new SummarizeTicketThreadJob($aiRun->id, $input);
        $job->handle();

        $aiRun->refresh();

        expect($aiRun->status)->toBe(AiRunStatus::Failed)
            ->and($aiRun->error_message)->toContain('API error')
            ->and($aiRun->completed_at)->not->toBeNull();
    }

    public function test_job_handles_rate_limit_error(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
        ]);

        ThreadSummaryAgent::fake([fn (): never => throw new \RuntimeException('Rate limit exceeded')]);

        $input = new ThreadSummaryInput(
            ticketId: $ticket->id,
            subject: 'Test',
            messageHistory: [['role' => 'requester', 'body' => 'Test message']],
        );

        $job = new SummarizeTicketThreadJob($aiRun->id, $input);
        $job->handle();

        $aiRun->refresh();

        expect($aiRun->status)->toBe(AiRunStatus::Failed)
            ->and($aiRun->error_message)->toContain('RATE_LIMIT:')
            ->and($aiRun->completed_at)->not->toBeNull();
    }
}

