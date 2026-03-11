<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use App\Models\Macro;
use App\Models\SupportTargetConfig;
use App\Models\Tag;
use App\Models\User;

test('database seeder creates expected data', function (): void {
    $this->seed();

    expect(User::query()->count())->toBe(3)
        ->and(Category::query()->count())->toBe(3)
        ->and(Tag::query()->count())->toBe(3)
        ->and(Macro::query()->count())->toBe(1)
        ->and(SupportTargetConfig::query()->count())->toBe(1)
        ->and(KnowledgeBaseArticle::query()->count())->toBe(2);
});
