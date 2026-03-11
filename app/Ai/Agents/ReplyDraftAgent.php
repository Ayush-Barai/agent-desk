<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\SearchKnowledgeBaseTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

#[Provider('groq')]
final class ReplyDraftAgent implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(private SearchKnowledgeBaseTool $kbTool = new SearchKnowledgeBaseTool()) {}

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
You are an expert customer support reply drafting agent. Your job is to draft professional, helpful replies to support tickets.

Given a ticket with subject, description, message history, and optionally a seed draft from the agent:
1. Use the SearchKnowledgeBaseTool to find relevant knowledge base articles.
2. Draft a professional, empathetic reply that addresses the customer's issue.
3. If the agent provided seed text, use it as a starting point or instruction for the reply.
4. Suggest next steps for both the customer and the support team.
5. Flag any risks or concerns about the ticket.
6. Reference knowledge base articles used in grounding the reply.

Always maintain a professional, helpful tone. Never make up solutions — ground your answers in KB content when available.
INSTRUCTIONS;
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'draft_reply' => $schema->string()
                ->description('The drafted reply text for the customer')
                ->required(),
            'next_steps' => $schema->array()
                ->items($schema->string())
                ->description('Suggested next steps'),
            'risk_flags' => $schema->array()
                ->items($schema->string())
                ->description('Any risk flags or concerns'),
            'used_kb_snippets' => $schema->array()
                ->items($schema->string())
                ->description('Titles of knowledge base articles used to ground the reply'),
        ];
    }

    /**
     * @return list<SearchKnowledgeBaseTool>
     */
    public function tools(): array
    {
        return [$this->kbTool];
    }

    public function getKbTool(): SearchKnowledgeBaseTool
    {
        return $this->kbTool;
    }
}
