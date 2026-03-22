<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\DTOs\ThreadSummaryInput;
use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Jobs\SummarizeTicketThreadJob;
use App\Models\AiRun;
use App\Models\Ticket;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class SummarizeTicketThreadJobTest extends TestCase
{
    public function test_job_marks_ai_run_as_running(): void
    {
        $ticket = Ticket::factory()->create();

        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ThreadSummary,
            'status' => AiRunStatus::Queued,
        ]);

        $input = new ThreadSummaryInput(
            ticketId: $ticket->id,
            subject: 'Test',
            messageHistory: [['role' => 'requester', 'body' => 'Test message']],
        );

        $job = new SummarizeTicketThreadJob($aiRun->id, $input);

        // Mock the AI response
        $mockResponse = new class {
            public string $text = '{"thread_summary":"Test summary","recommended_next_action":"Follow up"}';

            public object $meta;

            public function __construct()
            {
                $this->meta = (object) [
                    'provider' => 'groq',
                    'model' => 'mixtral-8x7b-32768',
                ];
            }
        };

        // We'll test the job logic manually since we can't easily mock the Ai facade
        // In this test, we're verifying the structure is correct
        $this->markTestIncomplete('Requires Ai facade mocking setup');
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
}
