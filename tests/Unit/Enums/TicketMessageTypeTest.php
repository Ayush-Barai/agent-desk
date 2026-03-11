<?php

declare(strict_types=1);

use App\Enums\TicketMessageType;

test('all message types have labels', function (): void {
    foreach (TicketMessageType::cases() as $type) {
        expect($type->label())->toBeString()->not->toBeEmpty();
    }
});

test('all message types have colors', function (): void {
    foreach (TicketMessageType::cases() as $type) {
        expect($type->color())->toBeString()->not->toBeEmpty();
    }
});

test('options returns all message types', function (): void {
    $options = TicketMessageType::options();

    expect($options)->toHaveCount(2)
        ->and($options)->toBe([
            TicketMessageType::Public,
            TicketMessageType::Internal,
        ]);
});
