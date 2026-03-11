<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\AiRun;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'role',
        ]);
});

test('default role is requester', function (): void {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Requester);
});

test('admin factory state', function (): void {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->isAdmin())->toBeTrue()
        ->and($user->isStaff())->toBeTrue()
        ->and($user->isRequester())->toBeFalse();
});

test('agent factory state', function (): void {
    $user = User::factory()->agent()->create();

    expect($user->role)->toBe(UserRole::Agent)
        ->and($user->isAgent())->toBeTrue()
        ->and($user->isStaff())->toBeTrue()
        ->and($user->isRequester())->toBeFalse();
});

test('requester factory state', function (): void {
    $user = User::factory()->requester()->create();

    expect($user->role)->toBe(UserRole::Requester)
        ->and($user->isRequester())->toBeTrue()
        ->and($user->isStaff())->toBeFalse()
        ->and($user->isAdmin())->toBeFalse();
});

test('user has many requester tickets', function (): void {
    $user = User::factory()->requester()->create();
    Ticket::factory()->create(['requester_id' => $user->id]);

    expect($user->requesterTickets)->toHaveCount(1)
        ->and($user->requesterTickets->first())->toBeInstanceOf(Ticket::class);
});

test('user has many assigned tickets', function (): void {
    $user = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($user)->create();

    expect($user->assignedTickets)->toHaveCount(1)
        ->and($user->assignedTickets->first())->toBeInstanceOf(Ticket::class);
});

test('user has many ticket messages', function (): void {
    $user = User::factory()->create();
    TicketMessage::factory()->create(['user_id' => $user->id]);

    expect($user->ticketMessages)->toHaveCount(1)
        ->and($user->ticketMessages->first())->toBeInstanceOf(TicketMessage::class);
});

test('user has many ai runs', function (): void {
    $user = User::factory()->agent()->create();
    AiRun::factory()->create(['initiated_by_user_id' => $user->id]);

    expect($user->aiRuns)->toHaveCount(1)
        ->and($user->aiRuns->first())->toBeInstanceOf(AiRun::class);
});

test('user has many audit logs', function (): void {
    $user = User::factory()->create();
    AuditLog::factory()->create(['actor_user_id' => $user->id]);

    expect($user->auditLogs)->toHaveCount(1)
        ->and($user->auditLogs->first())->toBeInstanceOf(AuditLog::class);
});
