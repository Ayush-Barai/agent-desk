<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\KnowledgeBaseArticleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $title
 * @property-read string $slug
 * @property-read string $body
 * @property-read string|null $excerpt
 * @property-read bool $is_published
 * @property-read string|null $search_text
 */
final class KnowledgeBaseArticle extends Model
{
    /** @use HasFactory<KnowledgeBaseArticleFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'body' => 'string',
            'excerpt' => 'string',
            'is_published' => 'boolean',
            'search_text' => 'string',
        ];
    }
}
