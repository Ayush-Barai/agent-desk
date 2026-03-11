<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TriageInput
{
    public function __construct(
        public string $ticketId,
        public string $subject,
        public string $description,
    ) {}
}
