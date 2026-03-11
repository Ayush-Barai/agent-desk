<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\TicketPriority;

final readonly class TriageResult
{
    /**
     * @param  list<string>  $tags
     * @param  list<string>  $clarifyingQuestions
     */
    public function __construct(
        public ?string $categorySuggestion,
        public ?TicketPriority $prioritySuggestion,
        public string $summary,
        public array $tags,
        public array $clarifyingQuestions,
        public bool $escalationRequired,
    ) {}
}
