<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\User;

test('admin can view audit logs', function (): void {
    $admin = User::factory()->admin()->create();
    $auditLog = AuditLog::factory()->create();

    expect($admin->can('viewAny', AuditLog::class))->toBeTrue()
        ->and($admin->can('view', $auditLog))->toBeTrue();
});

test('agent cannot view audit logs', function (): void {
    $agent = User::factory()->agent()->create();
    $auditLog = AuditLog::factory()->create();

    expect($agent->can('viewAny', AuditLog::class))->toBeFalse()
        ->and($agent->can('view', $auditLog))->toBeFalse();
});

test('requester cannot view audit logs', function (): void {
    $requester = User::factory()->requester()->create();

    expect($requester->can('viewAny', AuditLog::class))->toBeFalse();
});
