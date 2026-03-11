<?php

declare(strict_types=1);

use App\Models\KnowledgeBaseArticle;
use App\Models\User;

test('admin can manage knowledge base articles', function (): void {
    $admin = User::factory()->admin()->create();
    $article = KnowledgeBaseArticle::factory()->create();

    expect($admin->can('viewAny', KnowledgeBaseArticle::class))->toBeTrue()
        ->and($admin->can('view', $article))->toBeTrue()
        ->and($admin->can('create', KnowledgeBaseArticle::class))->toBeTrue()
        ->and($admin->can('update', $article))->toBeTrue()
        ->and($admin->can('delete', $article))->toBeTrue();
});

test('agent cannot manage knowledge base articles', function (): void {
    $agent = User::factory()->agent()->create();
    $article = KnowledgeBaseArticle::factory()->create();

    expect($agent->can('viewAny', KnowledgeBaseArticle::class))->toBeFalse()
        ->and($agent->can('view', $article))->toBeFalse()
        ->and($agent->can('create', KnowledgeBaseArticle::class))->toBeFalse()
        ->and($agent->can('update', $article))->toBeFalse()
        ->and($agent->can('delete', $article))->toBeFalse();
});

test('requester cannot manage knowledge base articles', function (): void {
    $requester = User::factory()->requester()->create();

    expect($requester->can('viewAny', KnowledgeBaseArticle::class))->toBeFalse()
        ->and($requester->can('create', KnowledgeBaseArticle::class))->toBeFalse();
});
