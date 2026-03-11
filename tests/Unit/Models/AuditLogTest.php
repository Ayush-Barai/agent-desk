<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;

test('audit log can be created via factory', function (): void {
    $auditLog = AuditLog::factory()->create();

    expect($auditLog->id)->toBeString()
        ->and($auditLog->action)->toBe('status_changed')
        ->and($auditLog->auditable_type)->toBe(Ticket::class);
});

test('audit log belongs to actor', function (): void {
    $user = User::factory()->create();
    $auditLog = AuditLog::factory()->create(['actor_user_id' => $user->id]);

    expect($auditLog->actor)->toBeInstanceOf(User::class)
        ->and($auditLog->actor->id)->toBe($user->id);
});

test('audit log belongs to ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $auditLog = AuditLog::factory()->create(['ticket_id' => $ticket->id]);

    expect($auditLog->ticket)->toBeInstanceOf(Ticket::class)
        ->and($auditLog->ticket->id)->toBe($ticket->id);
});

test('audit log has polymorphic auditable', function (): void {
    $ticket = Ticket::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'ticket_id' => $ticket->id,
        'auditable_type' => Ticket::class,
        'auditable_id' => $ticket->id,
    ]);

    expect($auditLog->auditable)->toBeInstanceOf(Ticket::class)
        ->and($auditLog->auditable->id)->toBe($ticket->id);
});
