<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Ai\Agents\ReplyDraftAgent;
use App\DTOs\KbSnippetDTO;
use App\DTOs\ReplyDraftInput;
use App\DTOs\ReplyDraftResult;
use App\Enums\AiRunStatus;
use App\Models\AiRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class DraftTicketReplyJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $aiRunId,
        public readonly ReplyDraftInput $input,
        public readonly string $seedText = '',
    ) {}

    /**
     * @param  list<string>  $titles
     * @param  list<KbSnippetDTO>  $retrievedSnippets
     * @return list<KbSnippetDTO>
     */
    public static function matchUsedSnippets(array $titles, array $retrievedSnippets): array
    {
        $matched = [];
        foreach ($titles as $title) {
            foreach ($retrievedSnippets as $snippet) {
                if ($snippet->title === $title) {
                    $matched[] = $snippet;

                    break;
                }
            }
        }

        return $matched;
    }

    public function handle(): void
    {
        $aiRun = AiRun::query()->findOrFail($this->aiRunId);

        $aiRun->update([
            'status' => AiRunStatus::Running,
            'progress_state' => 'Retrieving',
            'started_at' => now(),
        ]);

        try {
            $agent = new ReplyDraftAgent();

            $prompt = $this->buildPrompt();

            $aiRun->update(['progress_state' => 'Drafting']);

            $response = $agent->prompt($prompt);

            /** @var array{draft_reply?: string, next_steps?: list<string>, risk_flags?: list<string>, used_kb_snippets?: list<string>} $data */
            $data = json_decode($response->text, true, 512, JSON_THROW_ON_ERROR);

            $kbTool = $agent->getKbTool();
            $retrievedSnippets = $kbTool->getRetrievedSnippets();

            $usedKbSnippets = self::matchUsedSnippets($data['used_kb_snippets'] ?? [], $retrievedSnippets);

            $result = new ReplyDraftResult(
                draftReply: $data['draft_reply'] ?? '',
                nextSteps: $data['next_steps'] ?? [],
                riskFlags: $data['risk_flags'] ?? [],
                usedKbSnippets: $usedKbSnippets,
            );

            $aiRun->update([
                'status' => AiRunStatus::Succeeded,
                'progress_state' => 'Ready',
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
                'output_json' => [
                    'draft_reply' => $result->draftReply,
                    'next_steps' => $result->nextSteps,
                    'risk_flags' => $result->riskFlags,
                    'used_kb_snippets' => array_map(
                        static fn (KbSnippetDTO $s): array => [
                            'article_id' => $s->articleId,
                            'title' => $s->title,
                            'slug' => $s->slug,
                            'excerpt' => $s->excerpt,
                        ],
                        $result->usedKbSnippets,
                    ),
                    'retrieved_kb_snippets' => array_map(
                        static fn (KbSnippetDTO $s): array => [
                            'article_id' => $s->articleId,
                            'title' => $s->title,
                            'slug' => $s->slug,
                            'excerpt' => $s->excerpt,
                        ],
                        $retrievedSnippets,
                    ),
                    'seed_text' => $this->seedText,
                ],
                'completed_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            $aiRun->update([
                'status' => AiRunStatus::Failed,
                'progress_state' => null,
                'error_message' => $throwable->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }

    private function buildPrompt(): string
    {
        $parts = [];

        $parts[] = sprintf('Ticket Subject: %s', $this->input->subject);
        $parts[] = sprintf('Ticket Description: %s', $this->input->description);

        if ($this->input->messageHistory !== []) {
            $parts[] = 'Message History:';
            foreach ($this->input->messageHistory as $message) {
                $parts[] = sprintf('[%s]: %s', $message['role'], $message['body']);
            }
        }

        if ($this->input->kbSnippets !== []) {
            $parts[] = 'Relevant Knowledge Base Snippets:';
            foreach ($this->input->kbSnippets as $snippet) {
                $parts[] = sprintf('- %s: %s', $snippet->title, $snippet->excerpt);
            }
        }

        if ($this->seedText !== '') {
            $parts[] = sprintf('Agent seed instruction/draft: %s', $this->seedText);
        }

        return implode("\n\n", $parts);
    }
}
