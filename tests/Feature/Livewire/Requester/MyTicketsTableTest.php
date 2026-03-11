<?php

declare(strict_types=1);

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Livewire\Requester\MyTicketsTable;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

test('requester can view their tickets list', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('requester.tickets.index'))
        ->assertOk();
});

test('requester sees only own tickets', function (): void {
    $requester = User::factory()->requester()->create();
    $other = User::factory()->requester()->create();

    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'My ticket']);
    Ticket::factory()->create(['requester_id' => $other->id, 'subject' => 'Not my ticket']);

    Livewire::actingAs($requester)
        ->test(MyTicketsTable::class)
        ->assertSee('My ticket')
        ->assertDontSee('Not my ticket');
});

test('requester can search tickets', function (): void {
    $requester = User::factory()->requester()->create();

    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'Billing issue']);
    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'Technical problem']);

    Livewire::actingAs($requester)
        ->test(MyTicketsTable::class)
        ->set('search', 'Billing')
        ->assertSee('Billing issue')
        ->assertDontSee('Technical problem');
});

test('requester can filter tickets by status', function (): void {
    $requester = User::factory()->requester()->create();

    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'New ticket', 'status' => TicketStatus::New]);
    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'Resolved ticket', 'status' => TicketStatus::Resolved]);

    Livewire::actingAs($requester)
        ->test(MyTicketsTable::class)
        ->set('status', TicketStatus::New->value)
        ->assertSee('New ticket')
        ->assertDontSee('Resolved ticket');
});

test('requester can filter tickets by priority', function (): void {
    $requester = User::factory()->requester()->create();

    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'Urgent ticket', 'priority' => TicketPriority::Urgent]);
    Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'Low ticket', 'priority' => TicketPriority::Low]);

    Livewire::actingAs($requester)
        ->test(MyTicketsTable::class)
        ->set('priority', TicketPriority::Urgent->value)
        ->assertSee('Urgent ticket')
        ->assertDontSee('Low ticket');
});

test('tickets list shows empty state', function (): void {
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(MyTicketsTable::class)
        ->assertSee('No tickets found');
});

test('tickets list shows create ticket link', function (): void {
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(MyTicketsTable::class)
        ->assertSee('New Ticket');
});
