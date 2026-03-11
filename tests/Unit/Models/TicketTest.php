<?php

declare(strict_types=1);

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\AiRun;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;

test('ticket can be created via factory', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket->id)->toBeString()
        ->and($ticket->subject)->toBeString()
        ->and($ticket->description)->toBeString()
        ->and($ticket->status)->toBe(TicketStatus::New)
        ->and($ticket->priority)->toBeNull()
        ->and($ticket->escalation_required)->toBeFalse();
});

test('triaged factory state', function (): void {
    $ticket = Ticket::factory()->triaged()->create();

    expect($ticket->status)->toBe(TicketStatus::Triaged)
        ->and($ticket->priority)->toBe(TicketPriority::Medium)
        ->and($ticket->triaged_at)->not->toBeNull();
});

test('in progress factory state', function (): void {
    $ticket = Ticket::factory()->inProgress()->create();

    expect($ticket->status)->toBe(TicketStatus::InProgress);
});

test('resolved factory state', function (): void {
    $ticket = Ticket::factory()->resolved()->create();

    expect($ticket->status)->toBe(TicketStatus::Resolved)
        ->and($ticket->resolved_at)->not->toBeNull();
});

test('urgent factory state', function (): void {
    $ticket = Ticket::factory()->urgent()->create();

    expect($ticket->priority)->toBe(TicketPriority::Urgent)
        ->and($ticket->escalation_required)->toBeTrue();
});

test('ticket belongs to requester', function (): void {
    $user = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $user->id]);

    expect($ticket->requester)->toBeInstanceOf(User::class)
        ->and($ticket->requester->id)->toBe($user->id);
});

test('ticket belongs to assignee', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($agent)->create();

    expect($ticket->assignee)->toBeInstanceOf(User::class)
        ->and($ticket->assignee->id)->toBe($agent->id);
});

test('ticket belongs to category', function (): void {
    $category = Category::factory()->create();
    $ticket = Ticket::factory()->create(['category_id' => $category->id]);

    expect($ticket->category)->toBeInstanceOf(Category::class)
        ->and($ticket->category->id)->toBe($category->id);
});

test('ticket has many messages', function (): void {
    $ticket = Ticket::factory()->create();
    TicketMessage::factory()->create(['ticket_id' => $ticket->id]);

    expect($ticket->messages)->toHaveCount(1);
});

test('ticket has many attachments', function (): void {
    $ticket = Ticket::factory()->create();
    TicketAttachment::factory()->create(['ticket_id' => $ticket->id]);

    expect($ticket->attachments)->toHaveCount(1);
});

test('ticket has many ai runs', function (): void {
    $ticket = Ticket::factory()->create();
    AiRun::factory()->create(['ticket_id' => $ticket->id]);

    expect($ticket->aiRuns)->toHaveCount(1);
});

test('ticket has many audit logs', function (): void {
    $ticket = Ticket::factory()->create();
    AuditLog::factory()->create(['ticket_id' => $ticket->id]);

    expect($ticket->auditLogs)->toHaveCount(1);
});

test('ticket belongs to many tags', function (): void {
    $ticket = Ticket::factory()->create();
    $tag = Tag::factory()->create();
    $ticket->tags()->attach($tag);

    expect($ticket->tags)->toHaveCount(1)
        ->and($ticket->tags->first())->toBeInstanceOf(Tag::class);
});
