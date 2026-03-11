<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\OverdueResolutionNotification;
use App\Notifications\OverdueResponseNotification;
use App\Notifications\RequesterRepliedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketResolvedNotification;
use Illuminate\Support\Facades\Notification;

test('ticket assigned notification stores correct data', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Test Ticket']);

    Notification::fake();
    $agent->notify(new TicketAssignedNotification($ticket, $admin));

    Notification::assertSentTo($agent, TicketAssignedNotification::class, function (TicketAssignedNotification $notification) use ($agent): bool {
        $data = $notification->toArray($agent);

        return $data['ticket_subject'] === 'Test Ticket'
            && isset($data['ticket_id'])
            && isset($data['assigned_by_name'])
            && isset($data['message']);
    });
});

test('ticket assigned notification via database channel', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    Notification::fake();
    $agent->notify(new TicketAssignedNotification($ticket, $admin));

    Notification::assertSentTo($agent, TicketAssignedNotification::class, function (TicketAssignedNotification $notification): bool {
        $channels = $notification->via(new stdClass());

        return $channels === ['database'];
    });
});

test('requester replied notification stores correct data', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Help Request']);

    Notification::fake();
    $agent->notify(new RequesterRepliedNotification($ticket));

    Notification::assertSentTo($agent, RequesterRepliedNotification::class, function (RequesterRepliedNotification $notification) use ($agent): bool {
        $data = $notification->toArray($agent);

        return $data['ticket_subject'] === 'Help Request'
            && isset($data['ticket_id'])
            && isset($data['message']);
    });
});

test('requester replied notification via database channel', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Notification::fake();
    $agent->notify(new RequesterRepliedNotification($ticket));

    Notification::assertSentTo($agent, RequesterRepliedNotification::class, fn (RequesterRepliedNotification $notification): bool => $notification->via(new stdClass()) === ['database']);
});

test('ticket resolved notification stores correct data', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Resolved Issue']);

    Notification::fake();
    $requester->notify(new TicketResolvedNotification($ticket));

    Notification::assertSentTo($requester, TicketResolvedNotification::class, function (TicketResolvedNotification $notification) use ($requester): bool {
        $data = $notification->toArray($requester);

        return $data['ticket_subject'] === 'Resolved Issue'
            && isset($data['ticket_id'])
            && isset($data['message']);
    });
});

test('ticket resolved notification via database channel', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create();

    Notification::fake();
    $requester->notify(new TicketResolvedNotification($ticket));

    Notification::assertSentTo($requester, TicketResolvedNotification::class, fn (TicketResolvedNotification $notification): bool => $notification->via(new stdClass()) === ['database']);
});

test('overdue response notification stores correct data', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Overdue Ticket']);

    Notification::fake();
    $agent->notify(new OverdueResponseNotification($ticket));

    Notification::assertSentTo($agent, OverdueResponseNotification::class, function (OverdueResponseNotification $notification) use ($agent): bool {
        $data = $notification->toArray($agent);

        return $data['ticket_subject'] === 'Overdue Ticket'
            && isset($data['ticket_id'])
            && str_contains((string) $data['message'], 'overdue for first response');
    });
});

test('overdue response notification via database channel', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Notification::fake();
    $agent->notify(new OverdueResponseNotification($ticket));

    Notification::assertSentTo($agent, OverdueResponseNotification::class, fn (OverdueResponseNotification $notification): bool => $notification->via(new stdClass()) === ['database']);
});

test('overdue resolution notification stores correct data', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Late Resolution']);

    Notification::fake();
    $agent->notify(new OverdueResolutionNotification($ticket));

    Notification::assertSentTo($agent, OverdueResolutionNotification::class, function (OverdueResolutionNotification $notification) use ($agent): bool {
        $data = $notification->toArray($agent);

        return $data['ticket_subject'] === 'Late Resolution'
            && isset($data['ticket_id'])
            && str_contains((string) $data['message'], 'overdue for resolution');
    });
});

test('overdue resolution notification via database channel', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Notification::fake();
    $agent->notify(new OverdueResolutionNotification($ticket));

    Notification::assertSentTo($agent, OverdueResolutionNotification::class, fn (OverdueResolutionNotification $notification): bool => $notification->via(new stdClass()) === ['database']);
});
