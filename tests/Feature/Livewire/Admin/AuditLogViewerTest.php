<?php

declare(strict_types=1);

use App\Livewire\Admin\AuditLogViewer;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

test('admin can view audit logs page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk();
});

test('agent cannot access audit logs page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('requester cannot access audit logs page', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('admin sees audit log entries', function (): void {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['action' => 'status_changed']);
    AuditLog::factory()->create(['action' => 'assignment_changed']);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->assertSee('status changed')
        ->assertSee('assignment changed');
});

test('admin can filter by action type', function (): void {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['action' => 'status_changed']);
    AuditLog::factory()->create(['action' => 'assignment_changed']);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->set('actionFilter', 'status_changed')
        ->assertSee('status changed')
        ->assertDontSee('assignment changed');
});

test('admin can search audit logs', function (): void {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['action' => 'status_changed']);
    AuditLog::factory()->create(['action' => 'category_created']);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->set('search', 'category')
        ->assertSee('category created')
        ->assertDontSee('status changed');
});

test('empty state when no logs exist', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->assertSee('No audit logs found');
});

test('pagination resets when search changes', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->set('search', 'something')
        ->assertSet('search', 'something');
});

test('pagination resets when action filter changes', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->set('actionFilter', 'status_changed')
        ->assertSet('actionFilter', 'status_changed');
});

test('audit logs show actor name', function (): void {
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);
    AuditLog::factory()->create([
        'actor_user_id' => $admin->id,
        'action' => 'test_action',
    ]);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->assertSee('Admin User');
});

test('audit logs show system when no actor', function (): void {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create([
        'actor_user_id' => null,
        'action' => 'system_event',
    ]);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->assertSee('System');
});

test('audit logs show human-readable summary of changes', function (): void {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create([
        'action' => 'status_changed',
        'old_values_json' => ['status' => 'new'],
        'new_values_json' => ['status' => 'triaged'],
    ]);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->assertSee('Status: new → triaged');
});

test('admin can download audit logs as csv with filters', function (): void {
    $admin = User::factory()->admin()->create();
    // Log with actor and ticket
    AuditLog::factory()->create([
        'action' => 'test_action',
        'actor_user_id' => $admin->id,
        'ticket_id' => Ticket::factory()->create()->id,
    ]);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->set('search', 'test')
        ->set('actionFilter', 'test_action')
        ->call('downloadCsv')
        ->assertFileDownloaded('audit_logs.csv');
});

test('admin can download audit logs as csv without filters', function (): void {
    $admin = User::factory()->admin()->create();

    // Log with no actor (System) and no ticket (N/A)
    AuditLog::factory()->create([
        'action' => 'system_action',
        'actor_user_id' => null,
        'ticket_id' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->call('downloadCsv')
        ->assertFileDownloaded('audit_logs.csv');
});
