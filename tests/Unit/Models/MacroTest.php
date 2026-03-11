<?php

declare(strict_types=1);

use App\Models\Macro;

test('macro can be created via factory', function (): void {
    $macro = Macro::factory()->create();

    expect($macro->id)->toBeString()
        ->and($macro->title)->toBeString()
        ->and($macro->body)->toBeString()
        ->and($macro->is_active)->toBeTrue();
});

test('inactive factory state', function (): void {
    $macro = Macro::factory()->inactive()->create();

    expect($macro->is_active)->toBeFalse();
});
