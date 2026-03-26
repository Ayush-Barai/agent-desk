<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\User;

test('it generates human readable summaries for simple status changes', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['status' => 'new'],
        'new_values_json' => ['status' => 'triaged'],
        'action' => 'status_updated',
    ]);

    expect($log->getSummary())->toBe('Status: new → triaged');
});

test('it handles null old values as "set to"', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['priority' => null],
        'new_values_json' => ['priority' => 'high'],
    ]);

    expect($log->getSummary())->toBe('Priority set to "high"');
});

test('it handles null new values as "cleared"', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['priority' => 'high'],
        'new_values_json' => ['priority' => null],
    ]);

    expect($log->getSummary())->toBe('Priority cleared (was "high")');
});

test('it skips keys with identical old and new values', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['status' => 'new', 'priority' => 'high'],
        'new_values_json' => ['status' => 'triaged', 'priority' => 'high'],
    ]);

    expect($log->getSummary())->toBe('Status: new → triaged');
});

test('it falls back to action name if no changes found', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['foo' => 'bar'],
        'new_values_json' => ['foo' => 'bar'],
        'action' => 'test_action_event',
    ]);

    expect($log->getSummary())->toBe('Test action event');
});

test('it formats boolean values', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['is_active' => true],
        'new_values_json' => ['is_active' => false],
    ]);

    expect($log->getSummary())->toBe('Active: Yes → No');
});

test('it formats array values', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['meta_json' => ['a' => 1]],
        'new_values_json' => ['meta_json' => ['b' => 2]],
    ]);

    expect($log->getSummary())->toBe('Meta json: data → data');
});

test('it formats null or empty values as "none"', function (): void {
    $log = AuditLog::factory()->make([
        'old_values_json' => ['title' => ''],
        'new_values_json' => ['title' => null],
    ]);

    // Since they both resolve to 'none', it should skip the key if it were formatValue directly,
    // but here getSummary compares raw values first.
    // If we want to hit line 133, we need raw values to be different but resolve to 'none'.
    // Actually, getSummary does `if ($oldVal === $newVal) continue;`
    // So if old is '' and new is null, they are NOT identical, so it proceeds to formatValue.

    expect($log->getSummary())->toBe('Title cleared (was "none")');
});

test('it resolves user and category names from IDs', function (): void {
    $user = User::factory()->create(['name' => 'John Doe']);
    $category = Category::factory()->create(['name' => 'General Support']);

    // Test User resolution
    $logUser = AuditLog::factory()->make([
        'old_values_json' => ['assigned_to_user_id' => null],
        'new_values_json' => ['assigned_to_user_id' => $user->id],
    ]);
    expect($logUser->getSummary())->toBe('Assignee set to "John Doe"');

    // Test Category resolution
    $logCat = AuditLog::factory()->make([
        'old_values_json' => ['category_id' => null],
        'new_values_json' => ['category_id' => $category->id],
    ]);
    expect($logCat->getSummary())->toBe('Category set to "General Support"');

    // Test Cache hit (line 148-150)
    // The previous call should have cached John Doe
    $logUserCache = AuditLog::factory()->make([
        'old_values_json' => ['actor_user_id' => $user->id],
        'new_values_json' => ['actor_user_id' => null],
    ]);
    expect($logUserCache->getSummary())->toBe('Actor user id cleared (was "John Doe")');
});

test('it truncates long values', function (): void {
    $longString = str_repeat('a', 50);
    $truncated = str_repeat('a', 27).'...';

    $log = AuditLog::factory()->make([
        'old_values_json' => ['title' => 'short'],
        'new_values_json' => ['title' => $longString],
    ]);

    expect($log->getSummary())->toContain($truncated);
});
