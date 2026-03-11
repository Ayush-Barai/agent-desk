<?php

declare(strict_types=1);

namespace Database\Seeders;

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
    }
}
