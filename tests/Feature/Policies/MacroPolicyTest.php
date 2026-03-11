<?php

declare(strict_types=1);

use App\Models\Macro;
use App\Models\User;

test('admin can manage macros', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create();

    expect($admin->can('viewAny', Macro::class))->toBeTrue()
        ->and($admin->can('view', $macro))->toBeTrue()
        ->and($admin->can('create', Macro::class))->toBeTrue()
        ->and($admin->can('update', $macro))->toBeTrue()
        ->and($admin->can('delete', $macro))->toBeTrue();
});

test('agent cannot manage macros', function (): void {
    $agent = User::factory()->agent()->create();
    $macro = Macro::factory()->create();

    expect($agent->can('viewAny', Macro::class))->toBeFalse()
        ->and($agent->can('view', $macro))->toBeFalse()
        ->and($agent->can('create', Macro::class))->toBeFalse()
        ->and($agent->can('update', $macro))->toBeFalse()
        ->and($agent->can('delete', $macro))->toBeFalse();
});

test('requester cannot manage macros', function (): void {
    $requester = User::factory()->requester()->create();

    expect($requester->can('viewAny', Macro::class))->toBeFalse()
        ->and($requester->can('create', Macro::class))->toBeFalse();
});
