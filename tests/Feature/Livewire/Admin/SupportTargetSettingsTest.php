<?php

declare(strict_types=1);

use App\Livewire\Admin\SupportTargetSettings;
use App\Models\SupportTargetConfig;
use App\Models\User;
use Livewire\Livewire;

test('admin can view targets page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.targets.index'))
        ->assertOk();
});

test('agent cannot access targets page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.targets.index'))
        ->assertForbidden();
});

test('requester cannot access targets page', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.targets.index'))
        ->assertForbidden();
});

test('mounts with existing config values', function (): void {
    $admin = User::factory()->admin()->create();
    SupportTargetConfig::factory()->create([
        'first_response_hours' => 8,
        'resolution_hours' => 48,
    ]);

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->assertSet('first_response_hours', 8)
        ->assertSet('resolution_hours', 48)
        ->assertSet('saved', false);
});

test('mounts with defaults when no config exists', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->assertSet('first_response_hours', 24)
        ->assertSet('resolution_hours', 72)
        ->assertSet('configId', '');
});

test('admin can save new config when none exists', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('first_response_hours', 12)
        ->set('resolution_hours', 96)
        ->call('save')
        ->assertSet('saved', true)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('support_target_configs', [
        'first_response_hours' => 12,
        'resolution_hours' => 96,
    ]);
});

test('admin can update existing config', function (): void {
    $admin = User::factory()->admin()->create();
    $config = SupportTargetConfig::factory()->create([
        'first_response_hours' => 24,
        'resolution_hours' => 72,
    ]);

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->assertSet('configId', $config->id)
        ->set('first_response_hours', 4)
        ->set('resolution_hours', 24)
        ->call('save')
        ->assertSet('saved', true)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('support_target_configs', [
        'id' => $config->id,
        'first_response_hours' => 4,
        'resolution_hours' => 24,
    ]);
});

test('first_response_hours is required', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('first_response_hours', 0)
        ->call('save')
        ->assertHasErrors(['first_response_hours']);
});

test('resolution_hours is required', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('resolution_hours', 0)
        ->call('save')
        ->assertHasErrors(['resolution_hours']);
});

test('first_response_hours cannot exceed 720', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('first_response_hours', 721)
        ->call('save')
        ->assertHasErrors(['first_response_hours']);
});

test('resolution_hours cannot exceed 2160', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->set('resolution_hours', 2161)
        ->call('save')
        ->assertHasErrors(['resolution_hours']);
});

test('config id is set after creating new config', function (): void {
    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin)
        ->test(SupportTargetSettings::class)
        ->assertSet('configId', '')
        ->set('first_response_hours', 6)
        ->set('resolution_hours', 48)
        ->call('save');

    $config = SupportTargetConfig::query()->first();
    $component->assertSet('configId', $config->id);
});
