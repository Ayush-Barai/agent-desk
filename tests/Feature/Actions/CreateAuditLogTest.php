<?php

declare(strict_types=1);

use App\Actions\CreateAuditLog;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;

test('creates audit log with all fields', function (): void {
    $user = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();
    $category = Category::factory()->create();

    $log = new CreateAuditLog()->execute(
        action: 'status_changed',
        actor: $user,
        ticketId: $ticket->id,
        auditable: $category,
        oldValues: ['status' => 'new'],
        newValues: ['status' => 'triaged'],
        meta: ['source' => 'test'],
    );

    expect($log->id)->toBeString()
        ->and($log->actor_user_id)->toBe($user->id)
        ->and($log->ticket_id)->toBe($ticket->id)
        ->and($log->auditable_type)->toBe(Category::class)
        ->and($log->auditable_id)->toBe($category->id)
        ->and($log->action)->toBe('status_changed')
        ->and($log->old_values_json)->toBe(['status' => 'new'])
        ->and($log->new_values_json)->toBe(['status' => 'triaged'])
        ->and($log->meta_json)->toBe(['source' => 'test']);
});

test('creates audit log with minimal fields', function (): void {
    $log = new CreateAuditLog()->execute(action: 'system_event');

    expect($log->actor_user_id)->toBeNull()
        ->and($log->ticket_id)->toBeNull()
        ->and($log->auditable_type)->toBeNull()
        ->and($log->auditable_id)->toBeNull()
        ->and($log->action)->toBe('system_event')
        ->and($log->old_values_json)->toBeNull()
        ->and($log->new_values_json)->toBeNull()
        ->and($log->meta_json)->toBeNull();
});

test('creates audit log with actor only', function (): void {
    $user = User::factory()->create();

    $log = new CreateAuditLog()->execute(
        action: 'login',
        actor: $user,
    );

    expect($log->actor_user_id)->toBe($user->id)
        ->and($log->action)->toBe('login');
});
