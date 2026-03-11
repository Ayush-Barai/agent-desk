<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ReplyDraftResult
{
    /**
     * @param  list<string>  $nextSteps
     * @param  list<string>  $riskFlags
     * @param  list<KbSnippetDTO>  $usedKbSnippets
     */
    public function __construct(
        public string $draftReply,
        public array $nextSteps,
        public array $riskFlags,
        public array $usedKbSnippets,
    ) {}
}
