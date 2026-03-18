<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class AiRateLimitExceededException extends Exception
{
    public function __construct(
        public readonly string $throttleKey,
        public readonly int $limit,
        public readonly int $retryAfter,
        string $message = '',
    ) {
        $displayMessage = $message ?: sprintf('Rate limit exceeded. Please try again in %d seconds.', $retryAfter);
        parent::__construct($displayMessage);
    }

    /**
     * Get a user-friendly message for display in UI.
     */
    public function getUserMessage(): string
    {
        if ($this->retryAfter < 60) {
            return sprintf('Rate limit hit. Try again in %d seconds.', $this->retryAfter);
        }

        $minutes = (int) ceil($this->retryAfter / 60);

        return sprintf('Rate limit hit. Try again in about %d minute(s).', $minutes);
    }

    /**
     * Check if this is a per-ticket limit or global limit based on throttle key.
     */
    public function isPerTicket(): bool
    {
        return str_contains($this->throttleKey, ':ticket:');
    }

    /**
     * Check if this is a global per-user limit.
     */
    public function isGlobal(): bool
    {
        return ! $this->isPerTicket();
    }
}
