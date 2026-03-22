<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Ai\Agents\ThreadSummaryAgent;
use App\DTOs\ThreadSummaryInput;
use App\DTOs\ThreadSummaryResult;
use App\Enums\AiRunStatus;
use App\Models\AiRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SummarizeTicketThreadJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $aiRunId,
        public readonly ThreadSummaryInput $input,
    ) {}

    public function handle(): void
    {
        $aiRun = AiRun::query()->findOrFail($this->aiRunId);

        $aiRun->update([
            'status' => AiRunStatus::Running,
            'started_at' => now(),
        ]);

        try {
            $messageHistoryText = implode("\n\n", array_map(
                fn (array $msg): string => sprintf('[%s]: %s', $msg['role'], $msg['body']),
                $this->input->messageHistory
            ));

            $prompt = "Ticket Subject: {$this->input->subject}\n\nMessage History:\n{$messageHistoryText}";

            $agent = new ThreadSummaryAgent();
            $response = $agent->prompt($prompt);

            /** @var array{thread_summary?: string, recommended_next_action?: string|null} $data */
            $data = json_decode($response->text, true, 512, JSON_THROW_ON_ERROR);

            $result = new ThreadSummaryResult(
                threadSummary: $data['thread_summary'] ?? '',
                recommendedNextAction: $data['recommended_next_action'] ?? null,
            );

            $aiRun->update([
                'status' => AiRunStatus::Succeeded,
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
                'output_json' => [
                    'thread_summary' => $result->threadSummary,
                    'recommended_next_action' => $result->recommendedNextAction,
                ],
                'completed_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            $errorMessage = $throwable->getMessage();
            $isRateLimitError = str_contains(mb_strtolower($errorMessage), 'rate limit');

            $aiRun->update([
                'status' => AiRunStatus::Failed,
                'error_message' => ($isRateLimitError ? 'RATE_LIMIT: ' : '').$errorMessage,
                'completed_at' => now(),
            ]);

            // Log the error for monitoring
            if ($isRateLimitError) {
                Log::warning('Groq rate limit hit', [
                    'ai_run_id' => $this->aiRunId,
                    'ticket_id' => $aiRun->ticket_id,
                    'error' => $errorMessage,
                ]);
            }
        }
    }
}
