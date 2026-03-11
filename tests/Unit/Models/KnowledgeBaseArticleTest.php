<?php

declare(strict_types=1);

use App\Models\KnowledgeBaseArticle;

test('knowledge base article can be created via factory', function (): void {
    $article = KnowledgeBaseArticle::factory()->create();

    expect($article->id)->toBeString()
        ->and($article->title)->toBeString()
        ->and($article->slug)->toBeString()
        ->and($article->body)->toBeString()
        ->and($article->is_published)->toBeTrue();
});

test('unpublished factory state', function (): void {
    $article = KnowledgeBaseArticle::factory()->unpublished()->create();

    expect($article->is_published)->toBeFalse();
});
