<?php

declare(strict_types=1);

use App\Livewire\Admin\CategoryManager;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

test('admin can view categories page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk();
});

test('agent cannot access categories page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('requester cannot access categories page', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('admin sees existing categories', function (): void {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'Billing']);
    Category::factory()->create(['name' => 'Technical']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->assertSee('Billing')
        ->assertSee('Technical');
});

test('admin can open create form', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->assertSet('showForm', false)
        ->call('openCreate')
        ->assertSet('showForm', true)
        ->assertSet('editingId', '')
        ->assertSet('name', '')
        ->assertSet('description', '')
        ->assertSet('is_active', true);
});

test('admin can create a category', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', 'New Category')
        ->set('description', 'A test category')
        ->set('is_active', true)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'description' => 'A test category',
        'is_active' => true,
    ]);
});

test('admin can create a category without description', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', 'No Desc Category')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'No Desc Category',
        'description' => null,
    ]);
});

test('admin can open edit form', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create([
        'name' => 'Original',
        'description' => 'Desc',
        'is_active' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openEdit', $category->id)
        ->assertSet('showForm', true)
        ->assertSet('editingId', $category->id)
        ->assertSet('name', 'Original')
        ->assertSet('description', 'Desc')
        ->assertSet('is_active', false);
});

test('admin can update a category', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openEdit', $category->id)
        ->set('name', 'New Name')
        ->set('description', 'Updated desc')
        ->set('is_active', false)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New Name',
        'description' => 'Updated desc',
        'is_active' => false,
    ]);
});

test('admin can delete a category', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'To Delete']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('deleteCategory', $category->id);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('admin can cancel form', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', 'Something')
        ->call('cancel')
        ->assertSet('showForm', false)
        ->assertSet('name', '')
        ->assertSet('editingId', '');
});

test('name is required to save category', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('name must be unique when creating category', function (): void {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'Existing']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', 'Existing')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('name unique rule ignores current category when editing', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Keep Same']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openEdit', $category->id)
        ->set('description', 'Updated only desc')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Keep Same',
        'description' => 'Updated only desc',
    ]);
});

test('open create resets form after editing', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Edited']);

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openEdit', $category->id)
        ->assertSet('editingId', $category->id)
        ->call('openCreate')
        ->assertSet('editingId', '')
        ->assertSet('name', '')
        ->assertSet('description', '');
});

test('admin can create inactive category', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryManager::class)
        ->call('openCreate')
        ->set('name', 'Inactive Cat')
        ->set('is_active', false)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Inactive Cat',
        'is_active' => false,
    ]);
});
