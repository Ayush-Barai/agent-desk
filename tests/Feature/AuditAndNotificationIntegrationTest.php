<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\KbArticleManager;
use App\Livewire\Admin\MacroManager;
use App\Livewire\Admin\SupportTargetSettings;
use App\Livewire\Agent\AgentTicketDetail;
use App\Livewire\Requester\TicketDetail;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use App\Models\Macro;
use App\Models\SupportTargetConfig;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\RequesterRepliedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketResolvedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('status change creates audit log', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::New,
        'assigned_to_user_id' => $agent->id,
    ]);

    Notification::fake();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('status', 'triaged')
        ->call('updateMetadata');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'status_changed',
        'actor_user_id' => $agent->id,
        'ticket_id' => $ticket->id,
    ]);
});

test('resolving ticket notifies requester', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::InProgress,
        'requester_id' => $requester->id,
        'assigned_to_user_id' => $agent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('status', 'resolved')
        ->call('updateMetadata');

    Notification::assertSentTo($requester, TicketResolvedNotification::class);
});

test('priority change creates audit log', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
        'priority' => null,
    ]);

    Notification::fake();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('priority', 'high')
        ->call('updateMetadata');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'priority_changed',
        'actor_user_id' => $agent->id,
        'ticket_id' => $ticket->id,
    ]);
});

test('category change creates audit log', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
        'category_id' => null,
    ]);
    $category = Category::factory()->create();

    Notification::fake();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('categoryId', $category->id)
        ->call('updateMetadata');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'category_changed',
        'actor_user_id' => $agent->id,
        'ticket_id' => $ticket->id,
    ]);
});

test('assignment change creates audit log and notifies assignee', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $otherAgent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('assigneeId', $otherAgent->id)
        ->call('assignTicket');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'assignment_changed',
        'actor_user_id' => $agent->id,
        'ticket_id' => $ticket->id,
    ]);

    Notification::assertSentTo($otherAgent, TicketAssignedNotification::class);
});

test('assign to me creates audit log and notification', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('assignToMe');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'assignment_changed',
        'actor_user_id' => $agent->id,
        'ticket_id' => $ticket->id,
    ]);

    Notification::assertSentTo($agent, TicketAssignedNotification::class);
});

test('unassigning ticket creates audit log but no notification', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('assigneeId', '')
        ->call('assignTicket');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'assignment_changed',
        'actor_user_id' => $agent->id,
    ]);

    Notification::assertNothingSent();
});

test('no audit log when values unchanged', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::New,
        'priority' => null,
        'category_id' => null,
        'assigned_to_user_id' => $agent->id,
    ]);

    Notification::fake();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('updateMetadata');

    expect(AuditLog::query()->count())->toBe(0);
});

test('requester reply notifies assigned agent', function (): void {
    Notification::fake();

    $requester = User::factory()->requester()->create();
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to_user_id' => $agent->id,
    ]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Here is more info')
        ->call('submitReply');

    Notification::assertSentTo($agent, RequesterRepliedNotification::class);
});

test('requester reply does not notify when no assignee', function (): void {
    Notification::fake();

    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to_user_id' => null,
    ]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Here is more info')
        ->call('submitReply');

    Notification::assertNothingSent();
});

test('category manager creates audit log on create', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', 'Audit Test Category')
        ->call('save');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'category_created',
        'actor_user_id' => $admin->id,
    ]);
});

test('category manager creates audit log on update', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openEdit', $category->id)
        ->set('name', 'New Name')
        ->call('save');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'category_updated',
        'actor_user_id' => $admin->id,
    ]);
});

test('category manager creates audit log on delete', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('deleteCategory', $category->id);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'category_deleted',
        'actor_user_id' => $admin->id,
    ]);
});

test('macro manager creates audit log on create', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', 'Audit Macro')
        ->set('body', 'Body text')
        ->call('save');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'macro_created',
        'actor_user_id' => $admin->id,
    ]);
});

test('macro manager creates audit log on delete', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('deleteMacro', $macro->id);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'macro_deleted',
        'actor_user_id' => $admin->id,
    ]);
});

test('kb article manager creates audit log on create', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'Audit Article')
        ->set('body', 'Body text')
        ->call('save');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'kb_article_created',
        'actor_user_id' => $admin->id,
    ]);
});

test('kb article manager creates audit log on delete', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('deleteArticle', $article->id);

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'kb_article_deleted',
        'actor_user_id' => $admin->id,
    ]);
});

test('support target settings creates audit log on save', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('first_response_hours', 12)
        ->set('resolution_hours', 48)
        ->call('save');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'config_created',
        'actor_user_id' => $admin->id,
    ]);
});

test('support target settings creates audit log on update', function (): void {
    $admin = User::factory()->admin()->create();
    SupportTargetConfig::factory()->create([
        'first_response_hours' => 24,
        'resolution_hours' => 72,
    ]);

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('first_response_hours', 8)
        ->call('save');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'config_updated',
        'actor_user_id' => $admin->id,
    ]);
});
