<?php

declare(strict_types=1);

use App\Enums\TicketPriority;

test('all priorities have labels', function (): void {
    foreach (TicketPriority::cases() as $priority) {
        expect($priority->label())->toBeString()->not->toBeEmpty();
    }
});

test('all priorities have colors', function (): void {
    foreach (TicketPriority::cases() as $priority) {
        expect($priority->color())->toBeString()->not->toBeEmpty();
    }
});

test('options returns all priorities', function (): void {
    $options = TicketPriority::options();

    expect($options)->toHaveCount(4)
        ->and($options)->toBe([
            TicketPriority::Low,
            TicketPriority::Medium,
            TicketPriority::High,
            TicketPriority::Urgent,
        ]);
});
