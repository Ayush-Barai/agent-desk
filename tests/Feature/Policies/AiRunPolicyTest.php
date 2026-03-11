<?php

declare(strict_types=1);

use App\Models\AiRun;
use App\Models\User;

test('staff can view ai runs', function (): void {
    $agent = User::factory()->agent()->create();
    $admin = User::factory()->admin()->create();
    $aiRun = AiRun::factory()->create();

    expect($agent->can('viewAny', AiRun::class))->toBeTrue()
        ->and($agent->can('view', $aiRun))->toBeTrue()
        ->and($admin->can('viewAny', AiRun::class))->toBeTrue()
        ->and($admin->can('view', $aiRun))->toBeTrue();
});

test('staff can create ai runs', function (): void {
    $agent = User::factory()->agent()->create();

    expect($agent->can('create', AiRun::class))->toBeTrue();
});

test('requester cannot access ai runs', function (): void {
    $requester = User::factory()->requester()->create();
    $aiRun = AiRun::factory()->create();

    expect($requester->can('viewAny', AiRun::class))->toBeFalse()
        ->and($requester->can('view', $aiRun))->toBeFalse()
        ->and($requester->can('create', AiRun::class))->toBeFalse();
});
