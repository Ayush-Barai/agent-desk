<?php

declare(strict_types=1);

use App\Enums\TicketMessageType;
use App\Livewire\Requester\TicketDetail;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('requester can view own ticket detail', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id, 'subject' => 'My test ticket']);

    $this->actingAs($requester)
        ->get(route('requester.tickets.show', $ticket))
        ->assertOk();
});

test('requester cannot view others ticket detail', function (): void {
    $requester = User::factory()->requester()->create();
    $other = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $other->id]);

    $this->actingAs($requester)
        ->get(route('requester.tickets.show', $ticket))
        ->assertForbidden();
});

test('ticket detail shows public messages only', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $requester->id,
        'type' => TicketMessageType::Public,
        'body' => 'Public message content',
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => User::factory()->agent()->create()->id,
        'type' => TicketMessageType::Internal,
        'body' => 'Secret internal note',
    ]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Public message content')
        ->assertDontSee('Secret internal note');
});

test('ticket detail shows ticket metadata', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create([
        'requester_id' => $requester->id,
        'subject' => 'Important ticket subject',
    ]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Important ticket subject');
});

test('requester can post a public reply', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'This is my reply to the ticket')
        ->call('submitReply')
        ->assertHasNoErrors();

    $reply = TicketMessage::query()->where('ticket_id', $ticket->id)
        ->where('user_id', $requester->id)
        ->first();

    expect($reply)->not->toBeNull()
        ->and($reply->type)->toBe(TicketMessageType::Public)
        ->and($reply->body)->toBe('This is my reply to the ticket');

    $ticket->refresh();
    expect($ticket->last_requester_message_at)->not->toBeNull();
});

test('requester can reply with attachments', function (): void {
    Storage::fake('local');
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);
    $file = UploadedFile::fake()->create('reply-doc.pdf', 128, 'application/pdf');

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'See attached file')
        ->set('replyAttachments', [$file])
        ->call('submitReply')
        ->assertHasNoErrors();

    $attachment = TicketAttachment::query()->where('ticket_id', $ticket->id)->first();
    expect($attachment)->not->toBeNull()
        ->and($attachment->original_name)->toBe('reply-doc.pdf')
        ->and($attachment->disk)->toBe('local');
});

test('reply validates required body', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', '')
        ->call('submitReply')
        ->assertHasErrors(['replyBody']);
});

test('ticket detail shows attachments', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'uploaded_by_user_id' => $requester->id,
        'original_name' => 'screenshot.png',
    ]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->assertSee('screenshot.png');
});

test('ticket detail hides ai draft messages', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => User::factory()->agent()->create()->id,
        'type' => TicketMessageType::Public,
        'is_ai_draft' => true,
        'body' => 'AI draft that should be hidden',
    ]);

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->assertDontSee('AI draft that should be hidden');
});

test('requester can remove a selected reply attachment', function (): void {
    Storage::fake('local');
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);
    $file = UploadedFile::fake()->create('reply.pdf', 128, 'application/pdf');

    Livewire::actingAs($requester)
        ->test(TicketDetail::class, ['ticket' => $ticket])
        ->set('replyAttachments', [$file])
        ->assertCount('replyAttachments', 1)
        ->call('removeReplyAttachment', 0)
        ->assertCount('replyAttachments', 0);
});
