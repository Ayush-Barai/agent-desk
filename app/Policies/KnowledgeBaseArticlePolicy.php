<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KnowledgeBaseArticle;
use App\Models\User;

final class KnowledgeBaseArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, KnowledgeBaseArticle $knowledgeBaseArticle): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, KnowledgeBaseArticle $knowledgeBaseArticle): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, KnowledgeBaseArticle $knowledgeBaseArticle): bool
    {
        return $user->isAdmin();
    }
}
