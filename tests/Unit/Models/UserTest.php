<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'role',
        ]);
});

test('default role is requester', function (): void {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::Requester);
});

test('admin factory state', function (): void {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->isAdmin())->toBeTrue()
        ->and($user->isStaff())->toBeTrue()
        ->and($user->isRequester())->toBeFalse();
});

test('agent factory state', function (): void {
    $user = User::factory()->agent()->create();

    expect($user->role)->toBe(UserRole::Agent)
        ->and($user->isAgent())->toBeTrue()
        ->and($user->isStaff())->toBeTrue()
        ->and($user->isRequester())->toBeFalse();
});

test('requester factory state', function (): void {
    $user = User::factory()->requester()->create();

    expect($user->role)->toBe(UserRole::Requester)
        ->and($user->isRequester())->toBeTrue()
        ->and($user->isStaff())->toBeFalse()
        ->and($user->isAdmin())->toBeFalse();
});
