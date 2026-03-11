<?php

declare(strict_types=1);

use App\Livewire\Admin\MacroManager;
use App\Models\Macro;
use App\Models\User;
use Livewire\Livewire;

test('admin can view macros page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.macros.index'))
        ->assertOk();
});

test('agent cannot access macros page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.macros.index'))
        ->assertForbidden();
});

test('requester cannot access macros page', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.macros.index'))
        ->assertForbidden();
});

test('admin sees existing macros', function (): void {
    $admin = User::factory()->admin()->create();
    Macro::factory()->create(['title' => 'Greeting']);
    Macro::factory()->create(['title' => 'Closing']);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->assertSee('Greeting')
        ->assertSee('Closing');
});

test('admin can open create macro form', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->assertSet('showForm', false)
        ->call('openCreate')
        ->assertSet('showForm', true)
        ->assertSet('editingId', '')
        ->assertSet('title', '')
        ->assertSet('body', '')
        ->assertSet('is_active', true);
});

test('admin can create a macro', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', 'New Macro')
        ->set('body', 'Hello, how can I help?')
        ->set('is_active', true)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('macros', [
        'title' => 'New Macro',
        'body' => 'Hello, how can I help?',
        'is_active' => true,
    ]);
});

test('admin can open edit macro form', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create([
        'title' => 'Original',
        'body' => 'Original body',
        'is_active' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openEdit', $macro->id)
        ->assertSet('showForm', true)
        ->assertSet('editingId', $macro->id)
        ->assertSet('title', 'Original')
        ->assertSet('body', 'Original body')
        ->assertSet('is_active', false);
});

test('admin can update a macro', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create(['title' => 'Old Title', 'body' => 'Old body']);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openEdit', $macro->id)
        ->set('title', 'New Title')
        ->set('body', 'New body')
        ->set('is_active', false)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('macros', [
        'id' => $macro->id,
        'title' => 'New Title',
        'body' => 'New body',
        'is_active' => false,
    ]);
});

test('admin can delete a macro', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create(['title' => 'To Delete']);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('deleteMacro', $macro->id);

    $this->assertDatabaseMissing('macros', ['id' => $macro->id]);
});

test('admin can cancel macro form', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', 'Something')
        ->set('body', 'Some body')
        ->call('cancel')
        ->assertSet('showForm', false)
        ->assertSet('title', '')
        ->assertSet('body', '')
        ->assertSet('editingId', '');
});

test('title is required to save macro', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', '')
        ->set('body', 'Some body')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

test('body is required to save macro', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', 'Valid Title')
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['body' => 'required']);
});

test('title must be unique when creating macro', function (): void {
    $admin = User::factory()->admin()->create();
    Macro::factory()->create(['title' => 'Existing']);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', 'Existing')
        ->set('body', 'Body text')
        ->call('save')
        ->assertHasErrors(['title' => 'unique']);
});

test('title unique rule ignores current macro when editing', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create(['title' => 'Keep Same', 'body' => 'Original']);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openEdit', $macro->id)
        ->set('body', 'Updated body only')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('macros', [
        'id' => $macro->id,
        'title' => 'Keep Same',
        'body' => 'Updated body only',
    ]);
});

test('open create resets form after editing macro', function (): void {
    $admin = User::factory()->admin()->create();
    $macro = Macro::factory()->create(['title' => 'Edited', 'body' => 'Body']);

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openEdit', $macro->id)
        ->assertSet('editingId', $macro->id)
        ->call('openCreate')
        ->assertSet('editingId', '')
        ->assertSet('title', '')
        ->assertSet('body', '');
});

test('admin can create inactive macro', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(MacroManager::class)
        ->call('openCreate')
        ->set('title', 'Inactive Macro')
        ->set('body', 'Inactive body')
        ->set('is_active', false)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('macros', [
        'title' => 'Inactive Macro',
        'is_active' => false,
    ]);
});
