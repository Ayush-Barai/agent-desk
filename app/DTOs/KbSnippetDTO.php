<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class KbSnippetDTO
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $slug,
        public string $excerpt,
    ) {}
}
