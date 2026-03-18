<?php

declare(strict_types=1);

use App\Ai\Agents\TriageAgent;
use App\DTOs\TriageInput;
use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Jobs\RunTicketTriageJob;
use App\Livewire\Agent\AgentTicketDetail;
use App\Models\AiRun;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Livewire;

test('agent can trigger ai triage and job is dispatched', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage');

    $this->assertDatabaseHas('ai_runs', [
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::Triage->value,
        'status' => AiRunStatus::Queued->value,
    ]);

    Bus::assertDispatched(RunTicketTriageJob::class);
});

test('ai run input hash is computed correctly', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'subject' => 'Test Subject',
        'description' => 'Test Description',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage');

    $expectedHash = hash('sha256', json_encode([
        'ticket_id' => $ticket->id,
        'subject' => 'Test Subject',
        'description' => 'Test Description',
    ], JSON_THROW_ON_ERROR));

    $this->assertDatabaseHas('ai_runs', [
        'ticket_id' => $ticket->id,
        'input_hash' => $expectedHash,
    ]);
});

test('duplicate triage with same input reuses cached result', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create(['name' => 'Billing', 'is_active' => true]);
    $ticket = Ticket::factory()->create();

    $inputHash = hash('sha256', json_encode([
        'ticket_id' => $ticket->id,
        'subject' => $ticket->subject,
        'description' => $ticket->description,
    ], JSON_THROW_ON_ERROR));

    $existingRun = AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::Triage,
        'input_hash' => $inputHash,
        'output_json' => [
            'category_suggestion' => 'Billing',
            'priority_suggestion' => 'high',
            'summary' => 'Cached summary',
            'tags' => [],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage')
        ->assertSet('categoryId', $category->id)
        ->assertSet('priority', 'high');

    Bus::assertNotDispatched(RunTicketTriageJob::class);

    // No new ai_runs created
    expect(AiRun::query()->where('ticket_id', $ticket->id)->count())->toBe(1);
});

test('requester cannot trigger ai triage', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage')
        ->assertForbidden();
});

test('run ticket triage job succeeds and updates ai run', function (): void {
    TriageAgent::fake([
        [
            'category_suggestion' => 'Billing',
            'priority_suggestion' => 'high',
            'summary' => 'User has a billing issue.',
            'tags' => ['billing', 'payment'],
            'clarifying_questions' => ['Which invoice number?'],
            'escalation_required' => false,
        ],
    ]);

    Category::factory()->create(['name' => 'Billing', 'is_active' => true]);

    $ticket = Ticket::factory()->create();
    $agent = User::factory()->agent()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::Triage,
        'status' => AiRunStatus::Queued,
        'input_hash' => hash('sha256', 'test'),
        'input_json' => [
            'ticket_id' => $ticket->id,
            'subject' => $ticket->subject,
            'description' => $ticket->description,
        ],
    ]);

    $input = new TriageInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
    );

    $job = new RunTicketTriageJob($aiRun->id, $input);
    $job->handle();

    $aiRun->refresh();

    expect($aiRun->status)->toBe(AiRunStatus::Succeeded)
        ->and($aiRun->output_json)->toBeArray()
        ->and($aiRun->output_json['summary'])->toBe('User has a billing issue.')
        ->and($aiRun->output_json['category_suggestion'])->toBe('Billing')
        ->and($aiRun->output_json['priority_suggestion'])->toBe('high')
        ->and($aiRun->output_json['tags'])->toBe(['billing', 'payment'])
        ->and($aiRun->output_json['clarifying_questions'])->toBe(['Which invoice number?'])
        ->and($aiRun->output_json['escalation_required'])->toBeFalse()
        ->and($aiRun->started_at)->not->toBeNull()
        ->and($aiRun->completed_at)->not->toBeNull()
        ->and($aiRun->provider)->not->toBeNull()
        ->and($aiRun->model)->not->toBeNull();

    TriageAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, $ticket->subject));
});

test('run ticket triage job handles failure gracefully', function (): void {
    TriageAgent::fake(fn (): never => throw new RuntimeException('API rate limit exceeded'));

    $ticket = Ticket::factory()->create();
    $agent = User::factory()->agent()->create();

    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $agent->id,
        'run_type' => AiRunType::Triage,
        'status' => AiRunStatus::Queued,
        'input_hash' => hash('sha256', 'test'),
    ]);

    $input = new TriageInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
    );

    $job = new RunTicketTriageJob($aiRun->id, $input);
    $job->handle();

    $aiRun->refresh();

    expect($aiRun->status)->toBe(AiRunStatus::Failed)
        ->and($aiRun->error_message)->toBe('RATE_LIMIT: API rate limit exceeded')
        ->and($aiRun->completed_at)->not->toBeNull();
});

test('triage result prefills category when valid category exists', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create(['name' => 'Billing', 'is_active' => true]);
    $ticket = Ticket::factory()->create();

    $inputHash = hash('sha256', json_encode([
        'ticket_id' => $ticket->id,
        'subject' => $ticket->subject,
        'description' => $ticket->description,
    ], JSON_THROW_ON_ERROR));

    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'input_hash' => $inputHash,
        'output_json' => [
            'category_suggestion' => 'Billing',
            'priority_suggestion' => 'medium',
            'summary' => 'Test',
            'tags' => [],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage')
        ->assertSet('categoryId', $category->id)
        ->assertSet('priority', 'medium');
});

test('triage result prefills tags when matching tags exist', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $tag1 = Tag::factory()->create(['name' => 'billing']);
    $tag2 = Tag::factory()->create(['name' => 'payment']);
    Tag::factory()->create(['name' => 'unrelated']);
    $ticket = Ticket::factory()->create();

    $inputHash = hash('sha256', json_encode([
        'ticket_id' => $ticket->id,
        'subject' => $ticket->subject,
        'description' => $ticket->description,
    ], JSON_THROW_ON_ERROR));

    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'input_hash' => $inputHash,
        'output_json' => [
            'category_suggestion' => null,
            'priority_suggestion' => 'low',
            'summary' => 'Test',
            'tags' => ['billing', 'payment'],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage')
        ->assertSet('selectedTagIds', [$tag1->id, $tag2->id]);
});

test('triage result does not prefill invalid category', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $inputHash = hash('sha256', json_encode([
        'ticket_id' => $ticket->id,
        'subject' => $ticket->subject,
        'description' => $ticket->description,
    ], JSON_THROW_ON_ERROR));

    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'input_hash' => $inputHash,
        'output_json' => [
            'category_suggestion' => 'Nonexistent Category',
            'priority_suggestion' => 'medium',
            'summary' => 'Test',
            'tags' => [],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage')
        ->assertSet('categoryId', '');
});

test('triage job includes available categories in prompt', function (): void {
    TriageAgent::fake([
        [
            'category_suggestion' => 'Billing',
            'priority_suggestion' => 'medium',
            'summary' => 'Test summary',
            'tags' => [],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    Category::factory()->create(['name' => 'Billing', 'is_active' => true]);
    Category::factory()->create(['name' => 'Technical', 'is_active' => true]);
    Category::factory()->create(['name' => 'Inactive', 'is_active' => false]);

    $ticket = Ticket::factory()->create();
    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'status' => AiRunStatus::Queued,
        'input_hash' => hash('sha256', 'test'),
    ]);

    $input = new TriageInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
    );

    $job = new RunTicketTriageJob($aiRun->id, $input);
    $job->handle();

    TriageAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'Billing') && str_contains($prompt->prompt, 'Technical') && ! str_contains($prompt->prompt, 'Inactive'));
});

test('triage run transitions through status correctly', function (): void {
    TriageAgent::fake([
        [
            'category_suggestion' => null,
            'priority_suggestion' => 'low',
            'summary' => 'Simple inquiry',
            'tags' => [],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    $ticket = Ticket::factory()->create();
    $aiRun = AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'status' => AiRunStatus::Queued,
        'input_hash' => hash('sha256', 'test'),
    ]);

    expect($aiRun->status)->toBe(AiRunStatus::Queued);

    $input = new TriageInput(
        ticketId: $ticket->id,
        subject: $ticket->subject,
        description: $ticket->description,
    );

    $job = new RunTicketTriageJob($aiRun->id, $input);
    $job->handle();

    $aiRun->refresh();
    expect($aiRun->status)->toBe(AiRunStatus::Succeeded);
});

test('admin can trigger ai triage', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($admin)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage');

    $this->assertDatabaseHas('ai_runs', [
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $admin->id,
        'run_type' => AiRunType::Triage->value,
    ]);

    Bus::assertDispatched(RunTicketTriageJob::class);
});

test('agent ticket detail shows triage results when succeeded', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'output_json' => [
            'category_suggestion' => 'Billing',
            'priority_suggestion' => 'high',
            'summary' => 'Customer billing dispute',
            'tags' => ['billing', 'dispute'],
            'clarifying_questions' => ['What invoice number?'],
            'escalation_required' => true,
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Customer billing dispute')
        ->assertSee('Billing')
        ->assertSee('High')
        ->assertSee('billing')
        ->assertSee('dispute')
        ->assertSee('What invoice number?')
        ->assertSee('Escalation Recommended');
});

test('agent ticket detail shows failed triage status', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->failed()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'error_message' => 'API rate limit exceeded',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('AI triage failed')
        ->assertSee('API rate limit exceeded');
});

test('agent ticket detail shows queued triage status', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    AiRun::factory()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'status' => AiRunStatus::Queued,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Queued');
});

test('ai run stores input json correctly', function (): void {
    Bus::fake([RunTicketTriageJob::class]);

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'subject' => 'My Specific Subject',
        'description' => 'My Specific Description',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('runAiTriage');

    $aiRun = AiRun::query()->where('ticket_id', $ticket->id)->firstOrFail();

    expect($aiRun->input_json)->toBe([
        'ticket_id' => $ticket->id,
        'subject' => 'My Specific Subject',
        'description' => 'My Specific Description',
    ]);
});

test('apply triage result handles null output json gracefully', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $aiRun = AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'output_json' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('applyTriageResult', $aiRun)
        ->assertOk();

    expect($ticket->fresh()->category_id)->toBeNull();
});

test('triage agent has correct structured output schema', function (): void {
    $agent = new TriageAgent();

    expect($agent)->toBeInstanceOf(HasStructuredOutput::class);
    expect($agent->instructions())->toBeString()->not->toBeEmpty();
});
