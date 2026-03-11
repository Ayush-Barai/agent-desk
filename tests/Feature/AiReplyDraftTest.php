<?php

declare(strict_types=1);

use App\Ai\Agents\ReplyDraftAgent;
use App\Ai\Tools\SearchKnowledgeBaseTool;
use App\DTOs\KbSnippetDTO;
use App\DTOs\ReplyDraftInput;
use App\DTOs\ReplyDraftResult;
use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Jobs\DraftTicketReplyJob;
use App\Livewire\Agent\AgentTicketDetail;
use App\Models\AiRun;
use App\Models\KnowledgeBaseArticle;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Bus;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Tools\Request;
use Livewire\Livewire;

test('agent can trigger reply draft and job is dispatched', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('generateReply');

    $this->assertDatabaseHas('ai_runs', [
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::ReplyDraft->value,
        'status' => AiRunStatus::Queued->value,
    ]);

    Bus::assertDispatched(DraftTicketReplyJob::class);
});

test('generate reply passes seed text from reply body', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Please explain the refund process')
        ->call('generateReply');

    Bus::assertDispatched(DraftTicketReplyJob::class, fn (DraftTicketReplyJob $job): bool => $job->seedText === 'Please explain the refund process');
});

test('generate reply without seed text dispatches with empty seed', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('generateReply');

    Bus::assertDispatched(DraftTicketReplyJob::class, fn (DraftTicketReplyJob $job): bool => $job->seedText === '');
});

test('requester cannot trigger reply draft', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('generateReply')
        ->assertForbidden();
});

test('draft ticket reply job succeeds and updates ai run', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
    ]);

    $fakeOutput = [
        'draft_reply' => 'Thank you for contacting us. Here is the resolution.',
        'next_steps' => ['Follow up in 24 hours'],
        'risk_flags' => ['Customer may escalate'],
        'used_kb_snippets' => ['Refund Policy'],
    ];

    ReplyDraftAgent::fake([$fakeOutput]);

    $input = new ReplyDraftInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
        messageHistory: [],
        kbSnippets: [],
    );

    $job = new DraftTicketReplyJob($aiRun->id, $input);
    $job->handle();

    $aiRun->refresh();

    expect($aiRun->status)->toBe(AiRunStatus::Succeeded)
        ->and($aiRun->progress_state)->toBe('Ready')
        ->and($aiRun->output_json['draft_reply'])->toBe('Thank you for contacting us. Here is the resolution.')
        ->and($aiRun->output_json['next_steps'])->toBe(['Follow up in 24 hours'])
        ->and($aiRun->output_json['risk_flags'])->toBe(['Customer may escalate'])
        ->and($aiRun->provider)->not->toBeNull()
        ->and($aiRun->completed_at)->not->toBeNull();
});

test('draft ticket reply job handles failure gracefully', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
    ]);

    ReplyDraftAgent::fake([fn (): never => throw new RuntimeException('API error')]);

    $input = new ReplyDraftInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
        messageHistory: [],
        kbSnippets: [],
    );

    $job = new DraftTicketReplyJob($aiRun->id, $input);
    $job->handle();

    $aiRun->refresh();

    expect($aiRun->status)->toBe(AiRunStatus::Failed)
        ->and($aiRun->error_message)->toBe('API error')
        ->and($aiRun->progress_state)->toBeNull();
});

test('draft ticket reply job includes seed text in prompt', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'subject' => 'Refund request',
        'description' => 'I want a refund',
    ]);

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
    ]);

    $fakeOutput = [
        'draft_reply' => 'Refund response text',
        'next_steps' => [],
        'risk_flags' => [],
        'used_kb_snippets' => [],
    ];

    ReplyDraftAgent::fake([$fakeOutput]);

    $input = new ReplyDraftInput(
        ticketId: $ticket->id,
        subject: 'Refund request',
        description: 'I want a refund',
        messageHistory: [],
        kbSnippets: [],
    );

    $job = new DraftTicketReplyJob($aiRun->id, $input, 'Explain the refund timeline');
    $job->handle();

    ReplyDraftAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'Explain the refund timeline')
        && str_contains($prompt->prompt, 'Refund request'));
});

test('draft ticket reply job includes message history in prompt', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
    ]);

    ReplyDraftAgent::fake([[
        'draft_reply' => 'Response',
        'next_steps' => [],
        'risk_flags' => [],
        'used_kb_snippets' => [],
    ]]);

    $input = new ReplyDraftInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
        messageHistory: [
            ['role' => 'requester', 'body' => 'I need help with billing'],
            ['role' => 'agent', 'body' => 'Let me check your account'],
        ],
        kbSnippets: [],
    );

    $job = new DraftTicketReplyJob($aiRun->id, $input);
    $job->handle();

    ReplyDraftAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'I need help with billing')
        && str_contains($prompt->prompt, 'Let me check your account'));
});

test('agent can apply draft reply to reply body', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'output_json' => [
            'draft_reply' => 'This is the AI-generated draft reply.',
            'next_steps' => [],
            'risk_flags' => [],
            'used_kb_snippets' => [],
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('applyDraftReply', $aiRun->id)
        ->assertSet('replyBody', 'This is the AI-generated draft reply.');
});

test('apply draft reply handles null output gracefully', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'output_json' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('applyDraftReply', $aiRun->id)
        ->assertSet('replyBody', '');
});

test('agent ticket detail shows succeeded reply draft', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'output_json' => [
            'draft_reply' => 'Here is a helpful reply for the customer.',
            'next_steps' => ['Follow up tomorrow'],
            'risk_flags' => ['Potential escalation'],
            'used_kb_snippets' => [
                ['article_id' => '1', 'title' => 'FAQ Article', 'slug' => 'faq', 'excerpt' => 'Common questions'],
            ],
            'retrieved_kb_snippets' => [
                ['article_id' => '2', 'title' => 'Setup Guide', 'slug' => 'setup', 'excerpt' => 'Getting started'],
            ],
            'seed_text' => '',
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Here is a helpful reply for the customer.')
        ->assertSee('Follow up tomorrow')
        ->assertSee('Potential escalation')
        ->assertSee('FAQ Article')
        ->assertSee('Setup Guide');
});

test('agent ticket detail shows failed reply draft status', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->failed()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'error_message' => 'Rate limit exceeded',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Reply draft failed')
        ->assertSee('Rate limit exceeded');
});

test('agent ticket detail shows progress state during draft generation', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->running()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'progress_state' => 'Drafting',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Drafting...');
});

test('agent ticket detail shows queued reply draft status', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
        'progress_state' => 'Retrieving',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Retrieving...');
});

test('reply draft stores input json correctly', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'subject' => 'Test Subject',
        'description' => 'Test Description',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'seed text here')
        ->call('generateReply');

    $aiRun = AiRun::query()
        ->where('ticket_id', $ticket->id)
        ->where('run_type', AiRunType::ReplyDraft->value)
        ->firstOrFail();

    expect($aiRun->input_json['subject'])->toBe('Test Subject')
        ->and($aiRun->input_json['description'])->toBe('Test Description')
        ->and($aiRun->input_json['seed_text'])->toBe('seed text here');
});

test('reply draft agent has correct structured output schema', function (): void {
    $agent = new ReplyDraftAgent();

    expect($agent)->toBeInstanceOf(HasStructuredOutput::class)
        ->and($agent)->toBeInstanceOf(HasTools::class)
        ->and($agent->instructions())->toBeString()->not->toBeEmpty()
        ->and($agent->tools())->toHaveCount(1)
        ->and($agent->tools()[0])->toBeInstanceOf(SearchKnowledgeBaseTool::class);
});

test('search knowledge base tool finds matching articles', function (): void {
    KnowledgeBaseArticle::factory()->create([
        'title' => 'Refund Policy Guide',
        'body' => 'Our refund policy allows returns within 30 days.',
        'excerpt' => 'Return and refund information',
        'is_published' => true,
    ]);

    KnowledgeBaseArticle::factory()->create([
        'title' => 'Shipping FAQ',
        'body' => 'Shipping takes 3-5 business days.',
        'excerpt' => 'Shipping details',
        'is_published' => true,
    ]);

    $tool = new SearchKnowledgeBaseTool();
    $result = $tool->handle(new Request(['query' => 'refund']));

    expect($result)->toContain('Refund Policy Guide')
        ->and($result)->not->toContain('Shipping FAQ')
        ->and($tool->getRetrievedSnippets())->toHaveCount(1)
        ->and($tool->getRetrievedSnippets()[0])->toBeInstanceOf(KbSnippetDTO::class)
        ->and($tool->getRetrievedSnippets()[0]->title)->toBe('Refund Policy Guide');
});

test('search knowledge base tool returns no results message', function (): void {
    $tool = new SearchKnowledgeBaseTool();
    $result = $tool->handle(new Request(['query' => 'nonexistent topic']));

    expect($result)->toBe('No relevant knowledge base articles found.')
        ->and($tool->getRetrievedSnippets())->toBeEmpty();
});

test('search knowledge base tool handles empty query', function (): void {
    $tool = new SearchKnowledgeBaseTool();
    $result = $tool->handle(new Request(['query' => '']));

    expect($result)->toBe('No query provided.');
});

test('search knowledge base tool excludes unpublished articles', function (): void {
    KnowledgeBaseArticle::factory()->create([
        'title' => 'Published Article about billing',
        'is_published' => true,
    ]);

    KnowledgeBaseArticle::factory()->unpublished()->create([
        'title' => 'Draft Article about billing',
    ]);

    $tool = new SearchKnowledgeBaseTool();
    $result = $tool->handle(new Request(['query' => 'billing']));

    expect($result)->toContain('Published Article about billing')
        ->and($result)->not->toContain('Draft Article about billing');
});

test('draft ticket reply job transitions through progress states', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
    ]);

    ReplyDraftAgent::fake([[
        'draft_reply' => 'Reply text',
        'next_steps' => [],
        'risk_flags' => [],
        'used_kb_snippets' => [],
    ]]);

    $input = new ReplyDraftInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
        messageHistory: [],
        kbSnippets: [],
    );

    $job = new DraftTicketReplyJob($aiRun->id, $input);
    $job->handle();

    $aiRun->refresh();

    expect($aiRun->status)->toBe(AiRunStatus::Succeeded)
        ->and($aiRun->progress_state)->toBe('Ready')
        ->and($aiRun->started_at)->not->toBeNull();
});

test('admin can trigger reply draft', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($admin)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('generateReply');

    $this->assertDatabaseHas('ai_runs', [
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $admin->id,
        'run_type' => AiRunType::ReplyDraft->value,
    ]);

    Bus::assertDispatched(DraftTicketReplyJob::class);
});

test('generate reply includes message history in dispatched job', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $agent = User::factory()->agent()->create();
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $requester->id,
        'type' => 'public',
        'body' => 'Help me with my account',
        'is_ai_draft' => false,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('generateReply');

    Bus::assertDispatched(DraftTicketReplyJob::class, fn (DraftTicketReplyJob $job): bool => count($job->input->messageHistory) === 1
        && $job->input->messageHistory[0]['body'] === 'Help me with my account'
        && $job->input->messageHistory[0]['role'] === 'requester');
});

test('generate reply excludes ai draft messages from history', function (): void {
    Bus::fake([DraftTicketReplyJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'type' => 'public',
        'body' => 'Real message',
        'is_ai_draft' => false,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'type' => 'public',
        'body' => 'AI draft message',
        'is_ai_draft' => true,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('generateReply');

    Bus::assertDispatched(DraftTicketReplyJob::class, fn (DraftTicketReplyJob $job): bool => count($job->input->messageHistory) === 1
        && $job->input->messageHistory[0]['body'] === 'Real message');
});

test('draft reply does not auto send', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'output_json' => [
            'draft_reply' => 'Auto-generated reply',
            'next_steps' => [],
            'risk_flags' => [],
            'used_kb_snippets' => [],
        ],
    ]);

    $component = Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('applyDraftReply', $aiRun->id)
        ->assertSet('replyBody', 'Auto-generated reply');

    $this->assertDatabaseMissing('ticket_messages', [
        'ticket_id' => $ticket->id,
        'body' => 'Auto-generated reply',
    ]);
});

test('search knowledge base tool uses excerpt fallback from body', function (): void {
    KnowledgeBaseArticle::factory()->create([
        'title' => 'No Excerpt Article about widgets',
        'body' => 'This is a very long body text about widgets that should be truncated for the snippet excerpt.',
        'excerpt' => null,
        'is_published' => true,
    ]);

    $tool = new SearchKnowledgeBaseTool();
    $result = $tool->handle(new Request(['query' => 'widgets']));

    expect($result)->toContain('No Excerpt Article about widgets')
        ->and($tool->getRetrievedSnippets()[0]->excerpt)->toContain('widgets');
});

test('draft ticket reply job includes kb snippets in prompt when provided', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::ReplyDraft,
        'status' => AiRunStatus::Queued,
    ]);

    ReplyDraftAgent::fake([[
        'draft_reply' => 'Reply',
        'next_steps' => [],
        'risk_flags' => [],
        'used_kb_snippets' => [],
    ]]);

    $input = new ReplyDraftInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
        messageHistory: [],
        kbSnippets: [
            new KbSnippetDTO(
                articleId: 'kb1',
                title: 'Password Reset Guide',
                slug: 'password-reset',
                excerpt: 'Steps to reset your password',
            ),
        ],
    );

    $job = new DraftTicketReplyJob($aiRun->id, $input);
    $job->handle();

    ReplyDraftAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'Password Reset Guide')
        && str_contains($prompt->prompt, 'Steps to reset your password'));
});

test('reply draft agent constructor accepts custom kb tool', function (): void {
    $customTool = new SearchKnowledgeBaseTool();
    $agent = new ReplyDraftAgent($customTool);

    expect($agent->getKbTool())->toBe($customTool);
});

test('reply draft result dto holds correct data', function (): void {
    $snippet = new KbSnippetDTO(
        articleId: 'id1',
        title: 'Test Article',
        slug: 'test-article',
        excerpt: 'Test excerpt',
    );

    $result = new ReplyDraftResult(
        draftReply: 'Draft text',
        nextSteps: ['Step 1'],
        riskFlags: ['Risk 1'],
        usedKbSnippets: [$snippet],
    );

    expect($result->draftReply)->toBe('Draft text')
        ->and($result->nextSteps)->toBe(['Step 1'])
        ->and($result->riskFlags)->toBe(['Risk 1'])
        ->and($result->usedKbSnippets)->toHaveCount(1)
        ->and($result->usedKbSnippets[0]->title)->toBe('Test Article');
});

test('reply draft input dto holds correct data', function (): void {
    $input = new ReplyDraftInput(
        ticketId: 'tid',
        subject: 'Subject',
        description: 'Description',
        messageHistory: [['role' => 'requester', 'body' => 'Help']],
        kbSnippets: [],
    );

    expect($input->ticketId)->toBe('tid')
        ->and($input->subject)->toBe('Subject')
        ->and($input->description)->toBe('Description')
        ->and($input->messageHistory)->toHaveCount(1);
});

test('search knowledge base tool returns description', function (): void {
    $tool = new SearchKnowledgeBaseTool();

    expect($tool->description())->toBeString()->not->toBeEmpty();
});

test('search knowledge base tool returns schema', function (): void {
    $tool = new SearchKnowledgeBaseTool();
    $schema = new JsonSchemaTypeFactory();

    $result = $tool->schema($schema);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('query');
});

test('draft ticket reply job matches used kb snippets against retrieved snippets', function (): void {
    $snippetA = new KbSnippetDTO(articleId: 'a1', title: 'Refund Policy', slug: 'refund-policy', excerpt: 'How to refund');
    $snippetB = new KbSnippetDTO(articleId: 'a2', title: 'Shipping Info', slug: 'shipping-info', excerpt: 'Shipping details');

    $matched = DraftTicketReplyJob::matchUsedSnippets(
        ['Refund Policy'],
        [$snippetA, $snippetB],
    );

    expect($matched)->toHaveCount(1)
        ->and($matched[0]->title)->toBe('Refund Policy');
});

test('draft ticket reply job match returns empty when no titles match', function (): void {
    $snippet = new KbSnippetDTO(articleId: 'a1', title: 'Refund Policy', slug: 'refund-policy', excerpt: 'How to refund');

    $matched = DraftTicketReplyJob::matchUsedSnippets(
        ['Nonexistent Article'],
        [$snippet],
    );

    expect($matched)->toBeEmpty();
});

test('draft ticket reply job match returns empty when retrieved snippets empty', function (): void {
    $matched = DraftTicketReplyJob::matchUsedSnippets(
        ['Refund Policy'],
        [],
    );

    expect($matched)->toBeEmpty();
});
