<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\ThreadSummaryAgent;
use App\DTOs\ThreadSummaryInput;
use PHPUnit\Framework\TestCase;

final class ThreadSummaryAgentTest extends TestCase
{
    public function test_agent_has_instructions(): void
    {
        $agent = new ThreadSummaryAgent();
        $instructions = $agent->instructions();

        expect($instructions)->toBeString()
            ->not->toBeEmpty()
            ->toContain('summarization');
    }

    public function test_agent_has_valid_schema(): void
    {
        $agent = new ThreadSummaryAgent();

        expect($agent)->toBeInstanceOf(ThreadSummaryAgent::class);
    }

    public function test_dto_structure(): void
    {
        $messageHistory = [
            ['role' => 'requester', 'body' => 'I have an issue'],
            ['role' => 'agent', 'body' => 'Let me help'],
        ];

        $input = new ThreadSummaryInput(
            ticketId: 'test-123',
            subject: 'Test Issue',
            messageHistory: $messageHistory,
        );

        expect($input->ticketId)->toBe('test-123');
        expect($input->subject)->toBe('Test Issue');
        expect($input->messageHistory)->toHaveCount(2);
        expect($input->messageHistory[0]['role'])->toBe('requester');
    }
}
