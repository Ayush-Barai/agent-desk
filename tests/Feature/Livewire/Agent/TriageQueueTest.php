<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Livewire\Agent\TriageQueue;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

test('agent can view triage queue page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('agent.triage.index'))
        ->assertOk();
});

test('requester cannot access triage queue', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('agent.triage.index'))
        ->assertForbidden();
});

test('triage queue shows new unassigned tickets', function (): void {
    $agent = User::factory()->agent()->create();
    $requester = User::factory()->requester()->create();

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'New unassigned ticket',
        'status' => TicketStatus::New,
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->assertSee('New unassigned ticket');
});

test('triage queue hides assigned tickets', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'Assigned ticket',
        'status' => TicketStatus::New,
        'assigned_to_user_id' => $agent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->assertDontSee('Assigned ticket');
});

test('triage queue hides non-new status tickets', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'Triaged ticket',
        'status' => TicketStatus::Triaged,
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->assertDontSee('Triaged ticket');
});

test('triage queue can search by subject', function (): void {
    $agent = User::factory()->agent()->create();

    Ticket::factory()->create([
        'subject' => 'Billing issue urgent',
        'status' => TicketStatus::New,
        'assigned_to_user_id' => null,
    ]);

    Ticket::factory()->create([
        'subject' => 'Technical problem',
        'status' => TicketStatus::New,
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->set('search', 'Billing')
        ->assertSee('Billing issue urgent')
        ->assertDontSee('Technical problem');
});

test('triage queue shows empty state when no tickets', function (): void {
    $agent = User::factory()->agent()->create();

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->assertSee('No tickets in triage queue.');
});

test('triage queue shows requester and category', function (): void {
    $agent = User::factory()->agent()->create();
    $requester = User::factory()->requester()->create(['name' => 'John Requester']);
    $category = Category::factory()->create(['name' => 'Billing']);

    Ticket::factory()->create([
        'requester_id' => $requester->id,
        'category_id' => $category->id,
        'subject' => 'Queue ticket',
        'status' => TicketStatus::New,
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->assertSee('John Requester')
        ->assertSee('Billing');
});

test('triage queue resets page on search', function (): void {
    $agent = User::factory()->agent()->create();

    Livewire::actingAs($agent)
        ->test(TriageQueue::class)
        ->set('search', 'test')
        ->assertSet('search', 'test');
});
