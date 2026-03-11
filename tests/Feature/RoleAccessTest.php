<?php

declare(strict_types=1);

use App\Models\User;

test('requester can access requester tickets', function (): void {
    $user = User::factory()->requester()->create();

    $this->actingAs($user)
        ->get(route('requester.tickets.index'))
        ->assertOk();
});

test('requester cannot access agent tickets', function (): void {
    $user = User::factory()->requester()->create();

    $this->actingAs($user)
        ->get(route('agent.tickets.index'))
        ->assertForbidden();
});

test('requester cannot access admin users', function (): void {
    $user = User::factory()->requester()->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('agent can access agent tickets', function (): void {
    $user = User::factory()->agent()->create();

    $this->actingAs($user)
        ->get(route('agent.tickets.index'))
        ->assertOk();
});

test('agent can access requester tickets', function (): void {
    $user = User::factory()->agent()->create();

    $this->actingAs($user)
        ->get(route('requester.tickets.index'))
        ->assertOk();
});

test('agent cannot access admin users', function (): void {
    $user = User::factory()->agent()->create();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admin can access all routes', function (): void {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('requester.tickets.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('agent.tickets.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertOk();
});

test('guest cannot access role protected routes', function (): void {
    $this->get(route('requester.tickets.index'))
        ->assertRedirect(route('login'));

    $this->get(route('agent.tickets.index'))
        ->assertRedirect(route('login'));

    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('all roles can access dashboard', function (): void {
    foreach (['admin', 'agent', 'requester'] as $role) {
        $user = User::factory()->{$role}()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
});
