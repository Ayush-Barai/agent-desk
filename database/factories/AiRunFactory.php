<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiRun>
 */
final class AiRunFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'initiated_by_user_id' => User::factory(),
            'run_type' => AiRunType::Triage,
            'status' => AiRunStatus::Queued,
            'input_hash' => hash('sha256', fake()->sentence()),
            'input_json' => ['subject' => fake()->sentence()],
            'output_json' => null,
            'provider' => null,
            'model' => null,
            'error_message' => null,
            'progress_state' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function running(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AiRunStatus::Running,
            'provider' => 'groq',
            'model' => 'llama-3.3-70b-versatile',
            'started_at' => now(),
        ]);
    }

    public function succeeded(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AiRunStatus::Succeeded,
            'provider' => 'groq',
            'model' => 'llama-3.3-70b-versatile',
            'output_json' => ['result' => 'success'],
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AiRunStatus::Failed,
            'provider' => 'groq',
            'model' => 'llama-3.3-70b-versatile',
            'error_message' => 'API rate limit exceeded',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);
    }

    public function replyDraft(): self
    {
        return $this->state(fn (array $attributes): array => [
            'run_type' => AiRunType::ReplyDraft,
        ]);
    }

    public function threadSummary(): self
    {
        return $this->state(fn (array $attributes): array => [
            'run_type' => AiRunType::ThreadSummary,
        ]);
    }
}
