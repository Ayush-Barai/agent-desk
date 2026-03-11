<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;

test('ticket attachment can be created via factory', function (): void {
    $attachment = TicketAttachment::factory()->create();

    expect($attachment->id)->toBeString()
        ->and($attachment->storage_path)->toBeString()
        ->and($attachment->disk)->toBe('local')
        ->and($attachment->original_name)->toBeString()
        ->and($attachment->mime_type)->toBe('application/pdf')
        ->and($attachment->size_bytes)->toBeInt();
});

test('attachment belongs to ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $attachment = TicketAttachment::factory()->create(['ticket_id' => $ticket->id]);

    expect($attachment->ticket)->toBeInstanceOf(Ticket::class)
        ->and($attachment->ticket->id)->toBe($ticket->id);
});

test('attachment belongs to message', function (): void {
    $message = TicketMessage::factory()->create();
    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $message->ticket_id,
        'ticket_message_id' => $message->id,
    ]);

    expect($attachment->message)->toBeInstanceOf(TicketMessage::class)
        ->and($attachment->message->id)->toBe($message->id);
});

test('attachment belongs to uploader', function (): void {
    $user = User::factory()->create();
    $attachment = TicketAttachment::factory()->create(['uploaded_by_user_id' => $user->id]);

    expect($attachment->uploader)->toBeInstanceOf(User::class)
        ->and($attachment->uploader->id)->toBe($user->id);
});
