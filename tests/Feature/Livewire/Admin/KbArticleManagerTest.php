<?php

declare(strict_types=1);

use App\Livewire\Admin\KbArticleManager;
use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Livewire\Livewire;

test('admin can view kb articles page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.kb-articles.index'))
        ->assertOk();
});

test('agent cannot access kb articles page', function (): void {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->get(route('admin.kb-articles.index'))
        ->assertForbidden();
});

test('requester cannot access kb articles page', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('admin.kb-articles.index'))
        ->assertForbidden();
});

test('admin sees existing articles', function (): void {
    $admin = User::factory()->admin()->create();
    KnowledgeBaseArticle::factory()->create(['title' => 'Password Reset Guide']);
    KnowledgeBaseArticle::factory()->create(['title' => 'Getting Started']);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->assertSee('Password Reset Guide')
        ->assertSee('Getting Started');
});

test('admin can open create article form', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->assertSet('showForm', false)
        ->call('openCreate')
        ->assertSet('showForm', true)
        ->assertSet('editingId', '')
        ->assertSet('title', '')
        ->assertSet('body', '')
        ->assertSet('excerpt', '')
        ->assertSet('is_published', true);
});

test('admin can create an article', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'New Article')
        ->set('body', 'Article body content')
        ->set('excerpt', 'Short summary')
        ->set('is_published', true)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('knowledge_base_articles', [
        'title' => 'New Article',
        'slug' => 'new-article',
        'body' => 'Article body content',
        'excerpt' => 'Short summary',
        'is_published' => true,
    ]);
});

test('admin can create article without excerpt', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'No Excerpt Article')
        ->set('body', 'Body content')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('knowledge_base_articles', [
        'title' => 'No Excerpt Article',
        'slug' => 'no-excerpt-article',
        'excerpt' => null,
    ]);
});

test('slug is auto-generated from title', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'How To Reset Your Password')
        ->set('body', 'Follow these steps...')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('knowledge_base_articles', [
        'title' => 'How To Reset Your Password',
        'slug' => 'how-to-reset-your-password',
    ]);
});

test('admin can open edit article form', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create([
        'title' => 'Original Title',
        'body' => 'Original body',
        'excerpt' => 'Original excerpt',
        'is_published' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openEdit', $article->id)
        ->assertSet('showForm', true)
        ->assertSet('editingId', $article->id)
        ->assertSet('title', 'Original Title')
        ->assertSet('body', 'Original body')
        ->assertSet('excerpt', 'Original excerpt')
        ->assertSet('is_published', false);
});

test('admin can update an article', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create(['title' => 'Old Title', 'body' => 'Old body']);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openEdit', $article->id)
        ->set('title', 'New Title')
        ->set('body', 'New body')
        ->set('excerpt', 'New excerpt')
        ->set('is_published', false)
        ->call('save')
        ->assertSet('showForm', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('knowledge_base_articles', [
        'id' => $article->id,
        'title' => 'New Title',
        'slug' => 'new-title',
        'body' => 'New body',
        'excerpt' => 'New excerpt',
        'is_published' => false,
    ]);
});

test('admin can delete an article', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create(['title' => 'To Delete']);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('deleteArticle', $article->id);

    $this->assertDatabaseMissing('knowledge_base_articles', ['id' => $article->id]);
});

test('admin can cancel article form', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'Something')
        ->set('body', 'Some body')
        ->call('cancel')
        ->assertSet('showForm', false)
        ->assertSet('title', '')
        ->assertSet('body', '')
        ->assertSet('excerpt', '')
        ->assertSet('editingId', '');
});

test('title is required to save article', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', '')
        ->set('body', 'Some body')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

test('body is required to save article', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'Valid Title')
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['body' => 'required']);
});

test('title must be unique when creating article', function (): void {
    $admin = User::factory()->admin()->create();
    KnowledgeBaseArticle::factory()->create(['title' => 'Existing']);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'Existing')
        ->set('body', 'Body text')
        ->call('save')
        ->assertHasErrors(['title' => 'unique']);
});

test('title unique rule ignores current article when editing', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create(['title' => 'Keep Same', 'body' => 'Original']);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openEdit', $article->id)
        ->set('body', 'Updated body only')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('knowledge_base_articles', [
        'id' => $article->id,
        'title' => 'Keep Same',
        'body' => 'Updated body only',
    ]);
});

test('open create resets form after editing article', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create(['title' => 'Edited', 'body' => 'Body']);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openEdit', $article->id)
        ->assertSet('editingId', $article->id)
        ->call('openCreate')
        ->assertSet('editingId', '')
        ->assertSet('title', '')
        ->assertSet('body', '')
        ->assertSet('excerpt', '');
});

test('admin can create unpublished article', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openCreate')
        ->set('title', 'Draft Article')
        ->set('body', 'Draft body')
        ->set('is_published', false)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('knowledge_base_articles', [
        'title' => 'Draft Article',
        'is_published' => false,
    ]);
});

test('open edit with null excerpt sets empty string', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create([
        'title' => 'No Excerpt',
        'body' => 'Body',
        'excerpt' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(KbArticleManager::class)
        ->call('openEdit', $article->id)
        ->assertSet('excerpt', '');
});
