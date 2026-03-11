<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

final class KbArticleManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $editingId = '';

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:10000')]
    public string $body = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    public bool $is_published = true;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $article = KnowledgeBaseArticle::query()->findOrFail($id);
        $this->editingId = $article->id;
        $this->title = $article->title;
        $this->body = $article->body;
        $this->excerpt = $article->excerpt ?? '';
        $this->is_published = $article->is_published;
        $this->showForm = true;
    }

    public function save(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'title' => ['required', 'string', 'max:255', Rule::unique('knowledge_base_articles', 'title')->ignore($this->editingId ?: null)],
            'body' => ['required', 'string', 'max:10000'],
            'excerpt' => ['nullable', 'string', 'max:500'],
        ];

        $this->validate($rules);

        $slug = Str::slug($this->title);

        if ($this->editingId !== '') {
            $article = KnowledgeBaseArticle::query()->findOrFail($this->editingId);
            abort_unless($user->can('update', $article), 403);
            $article->update([
                'title' => $this->title,
                'slug' => $slug,
                'body' => $this->body,
                'excerpt' => $this->excerpt !== '' ? $this->excerpt : null,
                'is_published' => $this->is_published,
            ]);
        } else {
            abort_unless($user->can('create', KnowledgeBaseArticle::class), 403);
            KnowledgeBaseArticle::query()->create([
                'title' => $this->title,
                'slug' => $slug,
                'body' => $this->body,
                'excerpt' => $this->excerpt !== '' ? $this->excerpt : null,
                'is_published' => $this->is_published,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function deleteArticle(string $id): void
    {
        /** @var User $user */
        $user = Auth::user();
        $article = KnowledgeBaseArticle::query()->findOrFail($id);
        abort_unless($user->can('delete', $article), 403);
        $article->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * @return LengthAwarePaginator<int, KnowledgeBaseArticle>
     */
    public function getArticles(): LengthAwarePaginator
    {
        return KnowledgeBaseArticle::query()->orderBy('title')->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.kb-article-manager', [
            'articles' => $this->getArticles(),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = '';
        $this->title = '';
        $this->body = '';
        $this->excerpt = '';
        $this->is_published = true;
        $this->resetValidation();
    }
}
