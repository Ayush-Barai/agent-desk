<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\User;

test('requester can view own ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    expect($requester->can('view', $ticket))->toBeTrue();
});

test('requester cannot view others ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $otherRequester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $otherRequester->id]);

    expect($requester->can('view', $ticket))->toBeFalse();
});

test('agent can view any ticket', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    expect($agent->can('view', $ticket))->toBeTrue();
});

test('admin can view any ticket', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    expect($admin->can('view', $ticket))->toBeTrue();
});

test('any authenticated user can view ticket list', function (): void {
    $requester = User::factory()->requester()->create();
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();

    expect($requester->can('viewAny', Ticket::class))->toBeTrue()
        ->and($agent->can('viewAny', Ticket::class))->toBeTrue()
        ->and($admin->can('viewAny', Ticket::class))->toBeTrue();
});

test('any authenticated user can create tickets', function (): void {
    $requester = User::factory()->requester()->create();

    expect($requester->can('create', Ticket::class))->toBeTrue();
});

test('only staff can update tickets', function (): void {
    $requester = User::factory()->requester()->create();
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    expect($requester->can('update', $ticket))->toBeFalse()
        ->and($agent->can('update', $ticket))->toBeTrue()
        ->and($admin->can('update', $ticket))->toBeTrue();
});

test('only staff can assign tickets', function (): void {
    $requester = User::factory()->requester()->create();
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    expect($requester->can('assign', $ticket))->toBeFalse()
        ->and($agent->can('assign', $ticket))->toBeTrue()
        ->and($admin->can('assign', $ticket))->toBeTrue();
});

test('only staff can change ticket status', function (): void {
    $requester = User::factory()->requester()->create();
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    expect($requester->can('changeStatus', $ticket))->toBeFalse()
        ->and($agent->can('changeStatus', $ticket))->toBeTrue();
});

test('only admin can delete tickets', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    expect($agent->can('delete', $ticket))->toBeFalse()
        ->and($admin->can('delete', $ticket))->toBeTrue();
});
