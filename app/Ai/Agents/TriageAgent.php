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
final class TriageAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
You are an expert support ticket triage agent. Your job is to analyze support tickets and produce structured triage output.

Given a ticket with a subject and description, you must:
1. Suggest the most appropriate category name from the available categories.
2. Suggest a priority level: low, medium, high, or urgent.
3. Write a concise summary of the ticket.
4. Suggest relevant tags as short lowercase strings.
5. Generate clarifying questions if the ticket lacks important details.
6. Determine if escalation is required (true only for urgent/critical issues, security threats, or data loss risks).

Always respond with valid structured output matching the schema.
INSTRUCTIONS;
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'category_suggestion' => $schema->string()
                ->description('Suggested category name, or null if uncertain')
                ->nullable(),
            'priority_suggestion' => $schema->string()
                ->enum(['low', 'medium', 'high', 'urgent'])
                ->description('Suggested priority level')
                ->required(),
            'summary' => $schema->string()
                ->description('Concise summary of the ticket')
                ->required(),
            'tags' => $schema->array()
                ->items($schema->string())
                ->description('List of relevant tag names'),
            'clarifying_questions' => $schema->array()
                ->items($schema->string())
                ->description('Questions to ask the requester for more information'),
            'escalation_required' => $schema->boolean()
                ->description('Whether this ticket requires immediate escalation')
                ->required(),
        ];
    }
}
