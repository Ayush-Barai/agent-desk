<?php

declare(strict_types=1);

use App\Enums\TicketMessageType;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;

test('ticket message can be created via factory', function (): void {
    $message = TicketMessage::factory()->create();

    expect($message->id)->toBeString()
        ->and($message->type)->toBe(TicketMessageType::Public)
        ->and($message->body)->toBeString()
        ->and($message->is_ai_draft)->toBeFalse();
});

test('internal factory state', function (): void {
    $message = TicketMessage::factory()->internal()->create();

    expect($message->type)->toBe(TicketMessageType::Internal);
});

test('ai draft factory state', function (): void {
    $message = TicketMessage::factory()->aiDraft()->create();

    expect($message->is_ai_draft)->toBeTrue();
});

test('message belongs to ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $message = TicketMessage::factory()->create(['ticket_id' => $ticket->id]);

    expect($message->ticket)->toBeInstanceOf(Ticket::class)
        ->and($message->ticket->id)->toBe($ticket->id);
});

test('message belongs to author', function (): void {
    $user = User::factory()->create();
    $message = TicketMessage::factory()->create(['user_id' => $user->id]);

    expect($message->author)->toBeInstanceOf(User::class)
        ->and($message->author->id)->toBe($user->id);
});

test('message belongs to ai run', function (): void {
    $aiRun = AiRun::factory()->create();
    $message = TicketMessage::factory()->create([
        'ticket_id' => $aiRun->ticket_id,
        'ai_run_id' => $aiRun->id,
    ]);

    expect($message->aiRun)->toBeInstanceOf(AiRun::class)
        ->and($message->aiRun->id)->toBe($aiRun->id);
});

test('message has many attachments', function (): void {
    $message = TicketMessage::factory()->create();
    TicketAttachment::factory()->create([
        'ticket_id' => $message->ticket_id,
        'ticket_message_id' => $message->id,
    ]);

    expect($message->attachments)->toHaveCount(1);
});
