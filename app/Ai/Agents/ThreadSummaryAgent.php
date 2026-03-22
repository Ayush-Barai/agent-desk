<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Provider('groq')]
final class ThreadSummaryAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
You are an expert support ticket summarization agent. Your job is to analyze support tickets and produce a concise summary of the entire conversation thread.

Given a ticket with subject and a complete message history:
1. Analyze the entire conversation thread to understand the issue progression.
2. Produce a concise summary that captures the core problem, context, and current status.
3. Identify any recommended next actions based on the conversation.

Always respond with valid structured output matching the schema.
INSTRUCTIONS;
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'thread_summary' => $schema->string()
                ->description('Concise summary of the entire ticket thread and conversation')
                ->required(),
            'recommended_next_action' => $schema->string()
                ->description('Recommended next action based on the thread, or null if unclear')
                ->nullable(),
        ];
    }
}
