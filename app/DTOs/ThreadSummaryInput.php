<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ThreadSummaryInput
{
    /**
     * @param  list<array{role: string, body: string}>  $messageHistory
     */
    public function __construct(
        public string $ticketId,
        public string $subject,
        public array $messageHistory,
    ) {}
}
