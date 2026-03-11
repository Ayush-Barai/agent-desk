<?php

declare(strict_types=1);

use App\Enums\TicketMessageType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

test('staff can view any ticket message', function (): void {
    $agent = User::factory()->agent()->create();
    $message = TicketMessage::factory()->create(['type' => TicketMessageType::Internal]);

    expect($agent->can('view', $message))->toBeTrue();
});

test('requester can view public messages', function (): void {
    $requester = User::factory()->requester()->create();
    $message = TicketMessage::factory()->create(['type' => TicketMessageType::Public]);

    expect($requester->can('view', $message))->toBeTrue();
});

test('requester cannot view internal messages', function (): void {
    $requester = User::factory()->requester()->create();
    $message = TicketMessage::factory()->internal()->create();

    expect($requester->can('view', $message))->toBeFalse();
});

test('staff can create public message on any ticket', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    expect($agent->can('createPublic', [TicketMessage::class, $ticket]))->toBeTrue();
});

test('requester can create public message on own ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    expect($requester->can('createPublic', [TicketMessage::class, $ticket]))->toBeTrue();
});

test('requester cannot create public message on others ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create();

    expect($requester->can('createPublic', [TicketMessage::class, $ticket]))->toBeFalse();
});

test('staff can create internal notes', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    expect($agent->can('createInternal', [TicketMessage::class, $ticket]))->toBeTrue();
});

test('requester cannot create internal notes', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    expect($requester->can('createInternal', [TicketMessage::class, $ticket]))->toBeFalse();
});
