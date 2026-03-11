<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use App\Models\Macro;
use App\Models\SupportTargetConfig;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@agentdesk.test',
        ]);

        User::factory()->agent()->create([
            'name' => 'Agent User',
            'email' => 'agent@agentdesk.test',
        ]);

        User::factory()->requester()->create([
            'name' => 'Requester User',
            'email' => 'requester@agentdesk.test',
        ]);

        Category::factory()->create(['name' => 'Billing']);
        Category::factory()->create(['name' => 'Technical Support']);
        Category::factory()->create(['name' => 'General Inquiry']);

        Tag::factory()->create(['name' => 'bug', 'color' => '#ef4444']);
        Tag::factory()->create(['name' => 'feature-request', 'color' => '#3b82f6']);
        Tag::factory()->create(['name' => 'urgent', 'color' => '#f59e0b']);

        Macro::factory()->create([
            'title' => 'Greeting',
            'body' => 'Hello! Thank you for reaching out. How can we help you today?',
        ]);

        SupportTargetConfig::factory()->create();

        KnowledgeBaseArticle::factory()->create([
            'title' => 'Getting Started',
            'slug' => 'getting-started',
            'body' => 'Welcome to AgentDesk. This guide will help you get started with our platform.',
        ]);

        KnowledgeBaseArticle::factory()->create([
            'title' => 'Password Reset',
            'slug' => 'password-reset',
            'body' => 'To reset your password, click the "Forgot Password" link on the login page.',
        ]);
    }
}
