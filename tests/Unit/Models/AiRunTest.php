<?php

declare(strict_types=1);

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

test('ai run can be created via factory', function (): void {
    $aiRun = AiRun::factory()->create();

    expect($aiRun->id)->toBeString()
        ->and($aiRun->run_type)->toBe(AiRunType::Triage)
        ->and($aiRun->status)->toBe(AiRunStatus::Queued)
        ->and($aiRun->input_hash)->toBeString();
});

test('running factory state', function (): void {
    $aiRun = AiRun::factory()->running()->create();

    expect($aiRun->status)->toBe(AiRunStatus::Running)
        ->and($aiRun->provider)->toBe('groq')
        ->and($aiRun->model)->toBe('llama-3.3-70b-versatile')
        ->and($aiRun->started_at)->not->toBeNull();
});

test('succeeded factory state', function (): void {
    $aiRun = AiRun::factory()->succeeded()->create();

    expect($aiRun->status)->toBe(AiRunStatus::Succeeded)
        ->and($aiRun->output_json)->toBeArray()
        ->and($aiRun->completed_at)->not->toBeNull();
});

test('failed factory state', function (): void {
    $aiRun = AiRun::factory()->failed()->create();

    expect($aiRun->status)->toBe(AiRunStatus::Failed)
        ->and($aiRun->error_message)->toBeString()
        ->and($aiRun->completed_at)->not->toBeNull();
});

test('reply draft factory state', function (): void {
    $aiRun = AiRun::factory()->replyDraft()->create();

    expect($aiRun->run_type)->toBe(AiRunType::ReplyDraft);
});

test('thread summary factory state', function (): void {
    $aiRun = AiRun::factory()->threadSummary()->create();

    expect($aiRun->run_type)->toBe(AiRunType::ThreadSummary);
});

test('ai run belongs to ticket', function (): void {
    $ticket = Ticket::factory()->create();
    $aiRun = AiRun::factory()->create(['ticket_id' => $ticket->id]);

    expect($aiRun->ticket)->toBeInstanceOf(Ticket::class)
        ->and($aiRun->ticket->id)->toBe($ticket->id);
});

test('ai run belongs to initiator', function (): void {
    $user = User::factory()->agent()->create();
    $aiRun = AiRun::factory()->create(['initiated_by_user_id' => $user->id]);

    expect($aiRun->initiator)->toBeInstanceOf(User::class)
        ->and($aiRun->initiator->id)->toBe($user->id);
});

test('ai run has many messages', function (): void {
    $aiRun = AiRun::factory()->create();
    TicketMessage::factory()->create([
        'ticket_id' => $aiRun->ticket_id,
        'ai_run_id' => $aiRun->id,
    ]);

    expect($aiRun->messages)->toHaveCount(1);
});
