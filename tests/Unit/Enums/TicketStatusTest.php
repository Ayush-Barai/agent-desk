<?php

declare(strict_types=1);

use App\Enums\TicketStatus;

test('all statuses have labels', function (): void {
    foreach (TicketStatus::cases() as $status) {
        expect($status->label())->toBeString()->not->toBeEmpty();
    }
});

test('all statuses have colors', function (): void {
    foreach (TicketStatus::cases() as $status) {
        expect($status->color())->toBeString()->not->toBeEmpty();
    }
});

test('options returns all statuses', function (): void {
    $options = TicketStatus::options();

    expect($options)->toHaveCount(5)
        ->and($options)->toBe([
            TicketStatus::New,
            TicketStatus::Triaged,
            TicketStatus::InProgress,
            TicketStatus::Waiting,
            TicketStatus::Resolved,
        ]);
});
