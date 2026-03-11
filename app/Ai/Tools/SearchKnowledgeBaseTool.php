<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\DTOs\KbSnippetDTO;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

final class SearchKnowledgeBaseTool implements Tool
{
    /**
     * @var list<KbSnippetDTO>
     */
    private array $retrievedSnippets = [];

    public function description(): string
    {
        return 'Search the knowledge base for articles relevant to a support ticket query. Returns matching article titles and excerpts.';
    }

    public function handle(Request $request): string
    {
        $query = (string) $request->string('query');

        if ($query === '') {
            return 'No query provided.';
        }

        $articles = KnowledgeBaseArticle::query()
            ->where('is_published', true)
            ->where(function (Builder $q) use ($query): void {
                $q->where('title', 'like', sprintf('%%%s%%', $query))
                    ->orWhere('body', 'like', sprintf('%%%s%%', $query))
                    ->orWhere('excerpt', 'like', sprintf('%%%s%%', $query));
            })
            ->limit(5)
            ->get();

        if ($articles->isEmpty()) {
            return 'No relevant knowledge base articles found.';
        }

        $this->retrievedSnippets = [];

        foreach ($articles as $article) {
            $this->retrievedSnippets[] = new KbSnippetDTO(
                articleId: $article->id,
                title: $article->title,
                slug: $article->slug,
                excerpt: $article->excerpt ?? mb_substr((string) $article->body, 0, 200),
            );
        }

        $results = [];
        foreach ($this->retrievedSnippets as $snippet) {
            $results[] = sprintf('Title: %s | Excerpt: %s', $snippet->title, $snippet->excerpt);
        }

        return implode("\n\n", $results);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('The search query to find relevant knowledge base articles')
                ->required(),
        ];
    }

    /**
     * @return list<KbSnippetDTO>
     */
    public function getRetrievedSnippets(): array
    {
        return $this->retrievedSnippets;
    }
}
