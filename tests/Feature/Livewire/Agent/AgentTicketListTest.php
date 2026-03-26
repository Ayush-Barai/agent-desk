<?php

declare(strict_types=1);

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Livewire\Agent\AgentTicketList;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

test('agent can view ticket list page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('agent.tickets.index'))
        ->assertOk();
});

test('requester cannot access agent ticket list', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('agent.tickets.index'))
        ->assertForbidden();
});

test('agent sees own tickets in mine scope', function (): void {
    $agent = User::factory()->agent()->create();
    $otherAgent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'My assigned ticket',
        'assigned_to_user_id' => $agent->id,
    ]);

    Ticket::factory()->create([
        'subject' => 'Other agent ticket',
        'assigned_to_user_id' => $otherAgent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->assertSet('scope', 'mine')
        ->assertSee('My assigned ticket')
        ->assertDontSee('Other agent ticket');
});

test('agent sees all tickets in all scope', function (): void {
    $agent = User::factory()->agent()->create();
    $otherAgent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'My assigned ticket',
        'assigned_to_user_id' => $agent->id,
    ]);

    Ticket::factory()->create([
        'subject' => 'Other agent ticket',
        'assigned_to_user_id' => $otherAgent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->set('scope', 'all')
        ->assertSee('My assigned ticket')
        ->assertSee('Other agent ticket');
});

test('agent can search tickets by subject', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'Billing question',
        'assigned_to_user_id' => $agent->id,
    ]);

    Ticket::factory()->create([
        'subject' => 'Login problem',
        'assigned_to_user_id' => $agent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->set('search', 'Billing')
        ->assertSee('Billing question')
        ->assertDontSee('Login problem');
});

test('agent can filter tickets by status', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'New status ticket',
        'assigned_to_user_id' => $agent->id,
        'status' => TicketStatus::New,
    ]);

    Ticket::factory()->create([
        'subject' => 'Resolved status ticket',
        'assigned_to_user_id' => $agent->id,
        'status' => TicketStatus::Resolved,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->set('status', TicketStatus::New->value)
        ->assertSee('New status ticket')
        ->assertDontSee('Resolved status ticket');
});

test('agent can filter tickets by priority', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'Urgent priority ticket',
        'assigned_to_user_id' => $agent->id,
        'priority' => TicketPriority::Urgent,
    ]);

    Ticket::factory()->create([
        'subject' => 'Low priority ticket',
        'assigned_to_user_id' => $agent->id,
        'priority' => TicketPriority::Low,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->set('priority', TicketPriority::Urgent->value)
        ->assertSee('Urgent priority ticket')
        ->assertDontSee('Low priority ticket');
});

test('ticket list shows empty state', function (): void {
    $agent = User::factory()->agent()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->assertSee('No tickets found');
});

test('ticket list shows unassigned label', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'Unassigned ticket',
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->set('scope', 'all')
        ->assertSee('Unassigned');
});

test('agent ticket list resets page on filter changes', function (): void {
    $agent = User::factory()->agent()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->set('search', 'test')
        ->assertSet('search', 'test')
        ->set('status', TicketStatus::New->value)
        ->assertSet('status', TicketStatus::New->value)
        ->set('priority', TicketPriority::High->value)
        ->assertSet('priority', TicketPriority::High->value)
        ->set('scope', 'all')
        ->assertSet('scope', 'all');
});

test('admin can delete ticket from list', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($admin)
        ->test(AgentTicketList::class)
        ->call('deleteTicket', $ticket)
        ->assertDispatched('ticket-deleted');

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('agent cannot delete ticket from list', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketList::class)
        ->call('deleteTicket', $ticket)
        ->assertForbidden();

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
});

test('admin scope defaults to all', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AgentTicketList::class)
        ->assertSet('scope', 'all');
});
