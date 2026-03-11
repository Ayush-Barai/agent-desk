<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

test('admin can manage categories', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    expect($admin->can('viewAny', Category::class))->toBeTrue()
        ->and($admin->can('view', $category))->toBeTrue()
        ->and($admin->can('create', Category::class))->toBeTrue()
        ->and($admin->can('update', $category))->toBeTrue()
        ->and($admin->can('delete', $category))->toBeTrue();
});

test('agent cannot manage categories', function (): void {
    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create();

    expect($agent->can('viewAny', Category::class))->toBeFalse()
        ->and($agent->can('view', $category))->toBeFalse()
        ->and($agent->can('create', Category::class))->toBeFalse()
        ->and($agent->can('update', $category))->toBeFalse()
        ->and($agent->can('delete', $category))->toBeFalse();
});

test('requester cannot manage categories', function (): void {
    $requester = User::factory()->requester()->create();
    $category = Category::factory()->create();

    expect($requester->can('viewAny', Category::class))->toBeFalse()
        ->and($requester->can('create', Category::class))->toBeFalse();
});
