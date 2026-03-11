<?php

declare(strict_types=1);

use App\Enums\AiRunType;

test('all run types have labels', function (): void {
    foreach (AiRunType::cases() as $type) {
        expect($type->label())->toBeString()->not->toBeEmpty();
    }
});

test('options returns all run types', function (): void {
    $options = AiRunType::options();

    expect($options)->toHaveCount(3)
        ->and($options)->toBe([
            AiRunType::Triage,
            AiRunType::ReplyDraft,
            AiRunType::ThreadSummary,
        ]);
});
