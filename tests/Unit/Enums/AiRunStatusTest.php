<?php

declare(strict_types=1);

use App\Enums\AiRunStatus;

test('all statuses have labels', function (): void {
    foreach (AiRunStatus::cases() as $status) {
        expect($status->label())->toBeString()->not->toBeEmpty();
    }
});

test('all statuses have colors', function (): void {
    foreach (AiRunStatus::cases() as $status) {
        expect($status->color())->toBeString()->not->toBeEmpty();
    }
});

test('options returns all statuses', function (): void {
    $options = AiRunStatus::options();

    expect($options)->toHaveCount(4)
        ->and($options)->toBe([
            AiRunStatus::Queued,
            AiRunStatus::Running,
            AiRunStatus::Succeeded,
            AiRunStatus::Failed,
        ]);
});
