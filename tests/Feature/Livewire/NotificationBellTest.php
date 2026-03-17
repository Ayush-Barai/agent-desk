<?php

declare(strict_types=1);

use App\Livewire\NotificationBell;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\RequesterRepliedNotification;
use App\Notifications\TicketAssignedNotification;
use Livewire\Livewire;

test('notification bell renders unread count', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $agent->notify(new TicketAssignedNotification($ticket, $admin));

    Livewire::actingAs($agent)
        ->test(NotificationBell::class)
        ->assertSee('1');
});

test('notification bell shows notification message', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Need Help']);

    $agent->notify(new TicketAssignedNotification($ticket, $admin));

    Livewire::actingAs($agent)
        ->test(NotificationBell::class)
        ->assertSee('Need Help');
});

test('notification bell markAsRead marks a single notification as read', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $agent->notify(new TicketAssignedNotification($ticket, $admin));

    $notificationId = $agent->unreadNotifications()->first()->id;

    Livewire::actingAs($agent)
        ->test(NotificationBell::class)
        ->call('markAsRead', $notificationId);

    expect($agent->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notification bell markAllAsRead clears all unread notifications', function (): void {
    $requester = User::factory()->requester()->create();
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'assigned_to_user_id' => $agent->id,
    ]);

    $agent->notify(new TicketAssignedNotification($ticket, $agent));
    $agent->notify(new RequesterRepliedNotification($ticket));

    expect($agent->unreadNotifications()->count())->toBe(2);

    Livewire::actingAs($agent)
        ->test(NotificationBell::class)
        ->call('markAllAsRead');

    expect($agent->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notification bell shows empty state when all read', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $agent->notify(new TicketAssignedNotification($ticket, $admin));
    $agent->notifications()->update(['read_at' => now()]);

    Livewire::actingAs($agent)
        ->test(NotificationBell::class)
        ->assertSeeHtml('all caught up');
});

test('notification bell only shows unread notifications', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $ticket1 = Ticket::factory()->create(['subject' => 'Unread Ticket']);
    $ticket2 = Ticket::factory()->create(['subject' => 'Read Ticket']);

    $agent->notify(new TicketAssignedNotification($ticket1, $admin));
    $agent->notify(new TicketAssignedNotification($ticket2, $admin));

    // Mark only the second one as read
    $agent->notifications()
        ->where('data->ticket_id', $ticket2->id)
        ->update(['read_at' => now()]);

    Livewire::actingAs($agent)
        ->test(NotificationBell::class)
        ->assertSee('Unread Ticket')
        ->assertDontSee('Read Ticket');
});

test('notification bell is not rendered for guests', function (): void {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});
