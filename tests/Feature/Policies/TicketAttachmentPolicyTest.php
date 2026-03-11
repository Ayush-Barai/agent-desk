<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;

test('staff can view any attachment', function (): void {
    $agent = User::factory()->agent()->create();
    $attachment = TicketAttachment::factory()->create();

    expect($agent->can('view', $attachment))->toBeTrue();
});

test('ticket owner can view attachment on own ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);
    $attachment = TicketAttachment::factory()->create(['ticket_id' => $ticket->id]);

    expect($requester->can('view', $attachment))->toBeTrue();
});

test('requester cannot view attachment on others ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $attachment = TicketAttachment::factory()->create();

    expect($requester->can('view', $attachment))->toBeFalse();
});

test('staff can create attachment on any ticket', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    expect($agent->can('create', [TicketAttachment::class, $ticket]))->toBeTrue();
});

test('ticket owner can create attachment on own ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    expect($requester->can('create', [TicketAttachment::class, $ticket]))->toBeTrue();
});

test('requester cannot create attachment on others ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create();

    expect($requester->can('create', [TicketAttachment::class, $ticket]))->toBeFalse();
});

test('only admin can delete attachments', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $attachment = TicketAttachment::factory()->create();

    expect($agent->can('delete', $attachment))->toBeFalse()
        ->and($admin->can('delete', $attachment))->toBeTrue();
});
