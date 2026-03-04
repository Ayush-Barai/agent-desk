<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('it correctly identifies user roles', function (): void {
    $requester = User::factory()->make(['role' => UserRole::Requester]);
    $agent = User::factory()->make(['role' => UserRole::Agent]);
    $admin = User::factory()->make(['role' => UserRole::Admin]);

    // Test isRequester
    expect($requester->isRequester())->toBeTrue()
        ->and($agent->isRequester())->toBeFalse();

    // Test isAgent
    expect($agent->isAgent())->toBeTrue()
        ->and($admin->isAgent())->toBeFalse();

    // Test isAdmin
    expect($admin->isAdmin())->toBeTrue()
        ->and($requester->isAdmin())->toBeFalse();
});

test('it casts attributes correctly', function (): void {
    $user = User::factory()->make([
        'role' => 'admin', // passing string to check cast
        'is_active' => 1,
    ]);

    expect($user->role)->toBeInstanceOf(UserRole::class)
        ->and($user->is_active)->toBeTrue()
        ->and($user->role)->toBe(UserRole::Admin);
});

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'role',        // Add this
            'is_active',   // Add this
            'created_at',
            'updated_at',
        ]);
});
