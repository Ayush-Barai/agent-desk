<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ReplyDraftInput
{
    /**
     * @param  list<array{role: string, body: string}>  $messageHistory
     * @param  list<KbSnippetDTO>  $kbSnippets
     */
    public function __construct(
        public string $ticketId,
        public string $subject,
        public string $description,
        public array $messageHistory,
        public array $kbSnippets,
    ) {}
}
