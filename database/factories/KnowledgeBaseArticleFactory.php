<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBaseArticle>
 */
final class KnowledgeBaseArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => fake()->paragraphs(3, true),
            'excerpt' => fake()->sentence(),
            'is_published' => true,
            'search_text' => null,
        ];
    }

    public function unpublished(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => false,
        ]);
    }
}
