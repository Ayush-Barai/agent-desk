<?php

declare(strict_types=1);

use App\DTOs\KbSnippetDTO;
use App\DTOs\ReplyDraftInput;
use App\DTOs\ReplyDraftResult;
use App\DTOs\ThreadSummaryInput;
use App\DTOs\ThreadSummaryResult;
use App\DTOs\TriageInput;
use App\DTOs\TriageResult;
use App\Enums\TicketPriority;

test('triage input can be constructed', function (): void {
    $dto = new TriageInput(
        ticketId: 'abc-123',
        subject: 'Test subject',
        description: 'Test description',
    );

    expect($dto->ticketId)->toBe('abc-123')
        ->and($dto->subject)->toBe('Test subject')
        ->and($dto->description)->toBe('Test description');
});

test('triage result can be constructed', function (): void {
    $dto = new TriageResult(
        categorySuggestion: 'Billing',
        prioritySuggestion: TicketPriority::High,
        summary: 'Billing issue summary',
        tags: ['billing', 'urgent'],
        clarifyingQuestions: ['What is your account number?'],
        escalationRequired: false,
    );

    expect($dto->categorySuggestion)->toBe('Billing')
        ->and($dto->prioritySuggestion)->toBe(TicketPriority::High)
        ->and($dto->summary)->toBe('Billing issue summary')
        ->and($dto->tags)->toBe(['billing', 'urgent'])
        ->and($dto->clarifyingQuestions)->toBe(['What is your account number?'])
        ->and($dto->escalationRequired)->toBeFalse();
});

test('reply draft input can be constructed', function (): void {
    $snippet = new KbSnippetDTO(
        articleId: 'kb-1',
        title: 'Password Reset',
        slug: 'password-reset',
        excerpt: 'How to reset password',
    );

    $dto = new ReplyDraftInput(
        ticketId: 'abc-123',
        subject: 'Help with login',
        description: 'I cannot log in',
        messageHistory: [['role' => 'requester', 'body' => 'Help me']],
        kbSnippets: [$snippet],
    );

    expect($dto->ticketId)->toBe('abc-123')
        ->and($dto->messageHistory)->toHaveCount(1)
        ->and($dto->kbSnippets)->toHaveCount(1);
});

test('reply draft result can be constructed', function (): void {
    $dto = new ReplyDraftResult(
        draftReply: 'Here is a draft reply',
        nextSteps: ['Follow up in 24 hours'],
        riskFlags: [],
        usedKbSnippets: [],
    );

    expect($dto->draftReply)->toBe('Here is a draft reply')
        ->and($dto->nextSteps)->toBe(['Follow up in 24 hours'])
        ->and($dto->riskFlags)->toBeEmpty()
        ->and($dto->usedKbSnippets)->toBeEmpty();
});

test('thread summary input can be constructed', function (): void {
    $dto = new ThreadSummaryInput(
        ticketId: 'abc-123',
        subject: 'Login issue',
        messageHistory: [['role' => 'agent', 'body' => 'Please try resetting']],
    );

    expect($dto->ticketId)->toBe('abc-123')
        ->and($dto->subject)->toBe('Login issue')
        ->and($dto->messageHistory)->toHaveCount(1);
});

test('thread summary result can be constructed', function (): void {
    $dto = new ThreadSummaryResult(
        threadSummary: 'User had login issues, resolved by password reset.',
        recommendedNextAction: 'Close ticket',
    );

    expect($dto->threadSummary)->toBe('User had login issues, resolved by password reset.')
        ->and($dto->recommendedNextAction)->toBe('Close ticket');
});

test('kb snippet dto can be constructed', function (): void {
    $dto = new KbSnippetDTO(
        articleId: 'kb-1',
        title: 'Getting Started',
        slug: 'getting-started',
        excerpt: 'Welcome to our platform.',
    );

    expect($dto->articleId)->toBe('kb-1')
        ->and($dto->title)->toBe('Getting Started')
        ->and($dto->slug)->toBe('getting-started')
        ->and($dto->excerpt)->toBe('Welcome to our platform.');
});
