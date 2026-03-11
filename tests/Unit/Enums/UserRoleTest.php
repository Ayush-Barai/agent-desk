<?php

declare(strict_types=1);

use App\Enums\UserRole;

test('all roles have labels', function (): void {
    foreach (UserRole::cases() as $role) {
        expect($role->label())->toBeString()->not->toBeEmpty();
    }
});

test('all roles have colors', function (): void {
    foreach (UserRole::cases() as $role) {
        expect($role->color())->toBeString()->not->toBeEmpty();
    }
});

test('options returns all roles', function (): void {
    $options = UserRole::options();

    expect($options)->toHaveCount(3)
        ->and($options)->toBe([UserRole::Admin, UserRole::Agent, UserRole::Requester]);
});
