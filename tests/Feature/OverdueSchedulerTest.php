<?php

declare(strict_types=1);
use App\Enums\TicketStatus;
use App\Jobs\CheckOverdueTargetsJob;
use App\Livewire\Agent\AgentTicketDetail;
use App\Livewire\Requester\TicketCreateForm;
use App\Models\SupportTargetConfig;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\OverdueResolutionNotification;
use App\Notifications\OverdueResponseNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('overdue response ticket triggers notification to assignee', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->overdueResponse()->assignedTo($agent)->create();

    new CheckOverdueTargetsJob()->handle();

    Notification::assertSentTo($agent, OverdueResponseNotification::class);
});

test('overdue resolution ticket triggers notification to assignee', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->overdueResolution()->assignedTo($agent)->create();

    new CheckOverdueTargetsJob()->handle();

    Notification::assertSentTo($agent, OverdueResolutionNotification::class);
});

test('non-overdue response ticket does not trigger notification', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'first_response_due_at' => now()->addHours(24),
        'first_responded_at' => null,
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNothingSent();
});

test('non-overdue resolution ticket does not trigger notification', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'resolution_due_at' => now()->addHours(72),
        'resolved_at' => null,
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNothingSent();
});

test('already responded ticket does not trigger overdue response notification', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'first_response_due_at' => now()->subHour(),
        'first_responded_at' => now()->subMinutes(30),
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNotSentTo($agent, OverdueResponseNotification::class);
});

test('already resolved ticket does not trigger overdue resolution notification', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'status' => TicketStatus::Resolved,
        'resolution_due_at' => now()->subHour(),
        'resolved_at' => now()->subMinutes(30),
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNotSentTo($agent, OverdueResolutionNotification::class);
});

test('repeated job runs do not duplicate response notifications', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->overdueResponse()->assignedTo($agent)->create();

    $job = new CheckOverdueTargetsJob();
    $job->handle();
    $job->handle();

    Notification::assertSentToTimes($agent, OverdueResponseNotification::class, 1);
});

test('repeated job runs do not duplicate resolution notifications', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->overdueResolution()->assignedTo($agent)->create();

    $job = new CheckOverdueTargetsJob();
    $job->handle();
    $job->handle();

    Notification::assertSentToTimes($agent, OverdueResolutionNotification::class, 1);
});

test('overdue response notification marks ticket as notified', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->overdueResponse()->assignedTo($agent)->create();

    new CheckOverdueTargetsJob()->handle();

    $ticket->refresh();
    expect($ticket->overdue_response_notified_at)->not->toBeNull();
});

test('overdue resolution notification marks ticket as notified', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->overdueResolution()->assignedTo($agent)->create();

    new CheckOverdueTargetsJob()->handle();

    $ticket->refresh();
    expect($ticket->overdue_resolution_notified_at)->not->toBeNull();
});

test('unassigned overdue ticket notifies admins', function (): void {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    User::factory()->agent()->create();

    Ticket::factory()->overdueResponse()->create([
        'assigned_to_user_id' => null,
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertSentTo($admin, OverdueResponseNotification::class);
});

test('resolved ticket status excludes from response overdue check', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'status' => TicketStatus::Resolved,
        'first_response_due_at' => now()->subHour(),
        'first_responded_at' => null,
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNotSentTo($agent, OverdueResponseNotification::class);
});

test('ticket without due dates does not trigger notifications', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'first_response_due_at' => null,
        'resolution_due_at' => null,
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNothingSent();
});

test('ticket creation sets due dates from support target config', function (): void {
    SupportTargetConfig::query()->create([
        'first_response_hours' => 8,
        'resolution_hours' => 48,
    ]);

    $user = User::factory()->requester()->create();

    Livewire::actingAs($user)->test(TicketCreateForm::class)
        ->set('subject', 'Test ticket subject')
        ->set('description', 'This is a test ticket description')
        ->call('submit');

    $ticket = Ticket::query()->latest()->first();

    expect($ticket->first_response_due_at)->not->toBeNull()
        ->and($ticket->resolution_due_at)->not->toBeNull()
        ->and($ticket->first_response_due_at->diffInHours(now(), true))->toBeLessThan(9)
        ->and($ticket->resolution_due_at->diffInHours(now(), true))->toBeLessThan(49);
});

test('ticket creation without support target config sets null due dates', function (): void {
    $user = User::factory()->requester()->create();

    Livewire::actingAs($user)->test(TicketCreateForm::class)
        ->set('subject', 'Test ticket subject')
        ->set('description', 'This is a test ticket description')
        ->call('submit');

    $ticket = Ticket::query()->latest()->first();

    expect($ticket->first_response_due_at)->toBeNull()
        ->and($ticket->resolution_due_at)->toBeNull();
});

test('agent first public reply sets first_responded_at', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
        'first_responded_at' => null,
    ]);

    Livewire::actingAs($agent)->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Here is my response to your issue.')
        ->set('replyType', 'public')
        ->call('submitReply');

    $ticket->refresh();
    expect($ticket->first_responded_at)->not->toBeNull();
});

test('agent second public reply does not update first_responded_at', function (): void {
    $agent = User::factory()->agent()->create();
    $firstResponseTime = now()->subHour();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
        'first_responded_at' => $firstResponseTime,
    ]);

    Livewire::actingAs($agent)->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Here is my second response.')
        ->set('replyType', 'public')
        ->call('submitReply');

    $ticket->refresh();
    expect($ticket->first_responded_at->timestamp)->toBe($firstResponseTime->timestamp);
});

test('agent internal note does not set first_responded_at', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'assigned_to_user_id' => $agent->id,
        'first_responded_at' => null,
    ]);

    Livewire::actingAs($agent)->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Internal note about this ticket.')
        ->set('replyType', 'internal')
        ->call('submitReply');

    $ticket->refresh();
    expect($ticket->first_responded_at)->toBeNull();
});

test('overdue response notification contains ticket data', function (): void {
    $ticket = Ticket::factory()->create(['subject' => 'Urgent Issue']);
    $notification = new OverdueResponseNotification($ticket);

    $data = $notification->toArray(new User());
    expect($data['ticket_id'])->toBe($ticket->id)
        ->and($data['ticket_subject'])->toBe('Urgent Issue')
        ->and($data['message'])->toContain('overdue for first response');
});

test('overdue resolution notification contains ticket data', function (): void {
    $ticket = Ticket::factory()->create(['subject' => 'Pending Fix']);
    $notification = new OverdueResolutionNotification($ticket);

    $data = $notification->toArray(new User());
    expect($data['ticket_id'])->toBe($ticket->id)
        ->and($data['ticket_subject'])->toBe('Pending Fix')
        ->and($data['message'])->toContain('overdue for resolution');
});

test('overdue response notification uses database channel', function (): void {
    $ticket = Ticket::factory()->create();
    $notification = new OverdueResponseNotification($ticket);

    expect($notification->via(new User()))->toBe(['database']);
});

test('overdue resolution notification uses database channel', function (): void {
    $ticket = Ticket::factory()->create();
    $notification = new OverdueResolutionNotification($ticket);

    expect($notification->via(new User()))->toBe(['database']);
});

test('both overdue response and resolution can trigger for same ticket', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'status' => TicketStatus::InProgress,
        'first_response_due_at' => now()->subHour(),
        'first_responded_at' => null,
        'resolution_due_at' => now()->subHour(),
        'resolved_at' => null,
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertSentTo($agent, OverdueResponseNotification::class);
    Notification::assertSentTo($agent, OverdueResolutionNotification::class);
});

test('overdue response already notified ticket is skipped', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'first_response_due_at' => now()->subHour(),
        'first_responded_at' => null,
        'overdue_response_notified_at' => now()->subMinutes(30),
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNotSentTo($agent, OverdueResponseNotification::class);
});

test('overdue resolution already notified ticket is skipped', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    Ticket::factory()->assignedTo($agent)->create([
        'status' => TicketStatus::InProgress,
        'resolution_due_at' => now()->subHour(),
        'resolved_at' => null,
        'overdue_resolution_notified_at' => now()->subMinutes(30),
    ]);

    new CheckOverdueTargetsJob()->handle();

    Notification::assertNotSentTo($agent, OverdueResolutionNotification::class);
});

test('job is idempotent across multiple runs', function (): void {
    Notification::fake();

    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->overdueResponse()->assignedTo($agent)->create();

    $job = new CheckOverdueTargetsJob();
    $job->handle();
    $job->handle();
    $job->handle();

    Notification::assertSentToTimes($agent, OverdueResponseNotification::class, 1);
    $ticket->refresh();
    expect($ticket->overdue_response_notified_at)->not->toBeNull();
});

test('overdue factory state creates overdueResponse ticket', function (): void {
    $ticket = Ticket::factory()->overdueResponse()->create();

    expect($ticket->first_response_due_at)->not->toBeNull()
        ->and($ticket->first_response_due_at->isPast())->toBeTrue()
        ->and($ticket->first_responded_at)->toBeNull()
        ->and($ticket->overdue_response_notified_at)->toBeNull();
});

test('overdue factory state creates overdueResolution ticket', function (): void {
    $ticket = Ticket::factory()->overdueResolution()->create();

    expect($ticket->resolution_due_at)->not->toBeNull()
        ->and($ticket->resolution_due_at->isPast())->toBeTrue()
        ->and($ticket->resolved_at)->toBeNull()
        ->and($ticket->overdue_resolution_notified_at)->toBeNull();
});
