<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Ai\Agents\TriageAgent;
use App\DTOs\TriageInput;
use App\DTOs\TriageResult;
use App\Enums\AiRunStatus;
use App\Enums\TicketPriority;
use App\Models\AiRun;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class RunTicketTriageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $aiRunId,
        public readonly TriageInput $input,
    ) {}

    public function handle(): void
    {
        $aiRun = AiRun::query()->findOrFail($this->aiRunId);

        $aiRun->update([
            'status' => AiRunStatus::Running,
            'started_at' => now(),
        ]);

        try {
            $categoryNames = Category::query()
                ->where('is_active', true)
                ->pluck('name')
                ->implode(', ');

            $prompt = "Ticket Subject: {$this->input->subject}\n\nTicket Description: {$this->input->description}";

            if ($categoryNames !== '') {
                $prompt .= '

Available categories: '.$categoryNames;
            }

            $agent = new TriageAgent();
            $response = $agent->prompt($prompt);

            /** @var array{category_suggestion?: string|null, priority_suggestion?: string, summary?: string, tags?: list<string>, clarifying_questions?: list<string>, escalation_required?: bool} $data */
            $data = json_decode($response->text, true, 512, JSON_THROW_ON_ERROR);

            $priority = TicketPriority::tryFrom($data['priority_suggestion'] ?? '');

            $result = new TriageResult(
                categorySuggestion: $data['category_suggestion'] ?? null,
                prioritySuggestion: $priority,
                summary: $data['summary'] ?? '',
                tags: $data['tags'] ?? [],
                clarifyingQuestions: $data['clarifying_questions'] ?? [],
                escalationRequired: (bool) ($data['escalation_required'] ?? false),
            );

            $aiRun->update([
                'status' => AiRunStatus::Succeeded,
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
                'output_json' => [
                    'category_suggestion' => $result->categorySuggestion,
                    'priority_suggestion' => $result->prioritySuggestion?->value,
                    'summary' => $result->summary,
                    'tags' => $result->tags,
                    'clarifying_questions' => $result->clarifyingQuestions,
                    'escalation_required' => $result->escalationRequired,
                ],
                'completed_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            $aiRun->update([
                'status' => AiRunStatus::Failed,
                'error_message' => $throwable->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
