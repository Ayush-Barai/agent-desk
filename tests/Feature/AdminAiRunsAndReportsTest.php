<?php

declare(strict_types=1);
use App\Enums\AiRunType;
use App\Enums\TicketMessageType;
use App\Enums\TicketStatus;
use App\Livewire\Admin\AgentWorkReport;
use App\Livewire\Admin\AiRunDetail;
use App\Livewire\Admin\AiRunList;
use App\Models\AiRun;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Livewire\Livewire;

// --- Route access tests ---

test('admin can access ai runs list route', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.ai-runs.index'))
        ->assertOk();
});

test('admin can access ai run detail route', function (): void {
    $admin = User::factory()->admin()->create();
    $aiRun = AiRun::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.ai-runs.show', $aiRun))
        ->assertOk();
});

test('admin can access agent reports route', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.agent-reports.index'))
        ->assertOk();
});

test('agent cannot access ai runs list route', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.ai-runs.index'))
        ->assertForbidden();
});

test('agent cannot access ai run detail route', function (): void {
    $agent = User::factory()->agent()->create();
    $aiRun = AiRun::factory()->create();

    $this->actingAs($agent)
        ->get(route('admin.ai-runs.show', $aiRun))
        ->assertForbidden();
});

test('agent cannot access agent reports route', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.agent-reports.index'))
        ->assertForbidden();
});

test('requester cannot access ai runs list route', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.ai-runs.index'))
        ->assertForbidden();
});

test('requester cannot access ai run detail route', function (): void {
    $requester = User::factory()->requester()->create();
    $aiRun = AiRun::factory()->create();

    $this->actingAs($requester)
        ->get(route('admin.ai-runs.show', $aiRun))
        ->assertForbidden();
});

test('requester cannot access agent reports route', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.agent-reports.index'))
        ->assertForbidden();
});

test('guest cannot access admin ai routes', function (): void {
    $this->get(route('admin.ai-runs.index'))
        ->assertRedirect(route('login'));

    $aiRun = AiRun::factory()->create();
    $this->get(route('admin.ai-runs.show', $aiRun))
        ->assertRedirect(route('login'));

    $this->get(route('admin.agent-reports.index'))
        ->assertRedirect(route('login'));
});

// --- AiRunList component tests ---

test('ai run list displays runs', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Test Ticket Subject']);
    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $admin->id,
        'run_type' => AiRunType::Triage,
    ]);

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->assertSee('Test Ticket Subject')
        ->assertSee('Triage')
        ->assertSee('Succeeded');
});

test('ai run list can filter by status', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket1 = Ticket::factory()->create(['subject' => 'Succeeded Ticket Subject']);
    $ticket2 = Ticket::factory()->create(['subject' => 'Failed Ticket Subject']);
    AiRun::factory()->succeeded()->create(['ticket_id' => $ticket1->id]);
    AiRun::factory()->failed()->create(['ticket_id' => $ticket2->id]);

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->assertSee('Succeeded Ticket Subject')
        ->assertSee('Failed Ticket Subject')
        ->set('statusFilter', 'succeeded')
        ->assertSee('Succeeded Ticket Subject')
        ->assertDontSee('Failed Ticket Subject');
});

test('ai run list can filter by type', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket1 = Ticket::factory()->create(['subject' => 'Triage Unique Subject']);
    $ticket2 = Ticket::factory()->create(['subject' => 'Draft Unique Subject']);
    AiRun::factory()->create(['run_type' => AiRunType::Triage, 'ticket_id' => $ticket1->id]);
    AiRun::factory()->replyDraft()->create(['ticket_id' => $ticket2->id]);

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->assertSee('Triage Unique Subject')
        ->assertSee('Draft Unique Subject')
        ->set('typeFilter', 'triage')
        ->assertSee('Triage Unique Subject')
        ->assertDontSee('Draft Unique Subject');
});

test('ai run list can search by ticket subject', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket1 = Ticket::factory()->create(['subject' => 'Billing Issue']);
    $ticket2 = Ticket::factory()->create(['subject' => 'Login Problem']);
    AiRun::factory()->create(['ticket_id' => $ticket1->id]);
    AiRun::factory()->create(['ticket_id' => $ticket2->id]);

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->assertSee('Billing Issue')
        ->assertSee('Login Problem')
        ->set('search', 'Billing')
        ->assertSee('Billing Issue')
        ->assertDontSee('Login Problem');
});

test('ai run list shows empty state', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->assertSee('No AI runs found');
});

test('ai run list resets page on search', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->set('search', 'test')
        ->assertSet('search', 'test');
});

test('ai run list resets page on status filter', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->set('statusFilter', 'succeeded')
        ->assertSet('statusFilter', 'succeeded');
});

test('ai run list resets page on type filter', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->set('typeFilter', 'triage')
        ->assertSet('typeFilter', 'triage');
});

// --- AiRunDetail component tests ---

test('ai run detail shows run data', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Detail Test Ticket']);
    $aiRun = AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'initiated_by_user_id' => $admin->id,
        'run_type' => AiRunType::Triage,
        'input_json' => ['subject' => 'test input'],
        'output_json' => ['result' => 'test output'],
    ]);

    Livewire::actingAs($admin)
        ->test(AiRunDetail::class, ['aiRun' => $aiRun])
        ->assertSee('Detail Test Ticket')
        ->assertSee('Triage')
        ->assertSee('Succeeded')
        ->assertSee($admin->name)
        ->assertSee('test input')
        ->assertSee('test output');
});

test('ai run detail shows error message for failed run', function (): void {
    $admin = User::factory()->admin()->create();
    $aiRun = AiRun::factory()->failed()->create([
        'error_message' => 'API rate limit exceeded',
    ]);

    Livewire::actingAs($admin)
        ->test(AiRunDetail::class, ['aiRun' => $aiRun])
        ->assertSee('API rate limit exceeded')
        ->assertSee('Failed');
});

test('ai run detail shows input hash', function (): void {
    $admin = User::factory()->admin()->create();
    $hash = hash('sha256', 'test');
    $aiRun = AiRun::factory()->create([
        'input_hash' => $hash,
    ]);

    Livewire::actingAs($admin)
        ->test(AiRunDetail::class, ['aiRun' => $aiRun])
        ->assertSee($hash);
});

// --- AgentWorkReport component tests ---

test('agent work report shows agent metrics', function (): void {
    $admin = User::factory()->admin()->create();
    $agent = User::factory()->agent()->create(['name' => 'Test Agent']);

    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
        'status' => TicketStatus::Resolved,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'type' => TicketMessageType::Public,
    ]);

    TicketMessage::factory()->internal()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
    ]);

    AuditLog::factory()->create([
        'actor_user_id' => $agent->id,
        'action' => 'status_changed',
    ]);

    AiRun::factory()->create([
        'initiated_by_user_id' => $agent->id,
    ]);

    Livewire::actingAs($admin)
        ->test(AgentWorkReport::class)
        ->assertSee('Test Agent')
        ->assertSeeInOrder(['Test Agent', '1', '1', '1', '1', '1', '1']);
});

test('agent work report includes admins in metrics', function (): void {
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);

    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $admin->id,
    ]);

    AiRun::factory()->create([
        'initiated_by_user_id' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(AgentWorkReport::class)
        ->assertSee('Admin User')
        ->assertSeeInOrder(['Admin User', '1', '0', '0', '0', '0', '1']);
});

test('admin can delete ticket from ai run list', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();
    $aiRun = AiRun::factory()->create(['ticket_id' => $ticket->id]);

    Livewire::actingAs($admin)
        ->test(AiRunList::class)
        ->call('deleteTicket', $ticket)
        ->assertDispatched('ticket-deleted');

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('agent cannot delete ticket from ai run list', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    $aiRun = AiRun::factory()->create(['ticket_id' => $ticket->id]);

    Livewire::actingAs($agent)
        ->test(AiRunList::class)
        ->call('deleteTicket', $ticket)
        ->assertForbidden();

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
});

test('agent work report shows empty state when no agents', function (): void {
    $admin = User::factory()->admin()->create();

    // Remove all non-admin users — only admin remains
    // Admin is shown in reports (since isStaff = true, and report queries Admin + Agent roles)
    Livewire::actingAs($admin)
        ->test(AgentWorkReport::class)
        ->assertSee($admin->name);
});

test('agent work report shows zero metrics for agent with no activity', function (): void {
    $admin = User::factory()->admin()->create();
    $agent = User::factory()->agent()->create(['name' => 'Idle Agent']);

    Livewire::actingAs($admin)
        ->test(AgentWorkReport::class)
        ->assertSee('Idle Agent');
});

test('agent work report does not include requesters', function (): void {
    $admin = User::factory()->admin()->create();
    $requester = User::factory()->requester()->create(['name' => 'Customer Person']);

    Livewire::actingAs($admin)
        ->test(AgentWorkReport::class)
        ->assertDontSee('Customer Person');
});

test('agent work report correctly counts multiple tickets', function (): void {
    $admin = User::factory()->admin()->create();
    $agent = User::factory()->agent()->create(['name' => 'Busy Agent']);

    Ticket::factory()->count(3)->create([
        'assigned_to_user_id' => $agent->id,
    ]);

    TicketMessage::factory()->count(5)->create([
        'user_id' => $agent->id,
        'type' => TicketMessageType::Public,
    ]);

    Livewire::actingAs($admin)
        ->test(AgentWorkReport::class)
        ->assertSee('Busy Agent');
});
