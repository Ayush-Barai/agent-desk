<?php

declare(strict_types=1);

use App\Models\SupportTargetConfig;

test('support target config can be created via factory', function (): void {
    $config = SupportTargetConfig::factory()->create();

    expect($config->id)->toBeString()
        ->and($config->first_response_hours)->toBe(24)
        ->and($config->resolution_hours)->toBe(72);
});
