<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

final readonly class AiRateLimiter
{
    private int $perTicketLimit;

    private int $perUserGlobalLimit;

    private int $windowSeconds;

    public function __construct()
    {
        $perTicketLimit = config('ai.rate_limits.triage_per_ticket_hour', 5);
        $perUserGlobalLimit = config('ai.rate_limits.global_per_user_hour', 20);
        $windowSeconds = config('ai.rate_limits.window_seconds', 3600);

        $this->perTicketLimit = is_numeric($perTicketLimit) ? (int) $perTicketLimit : 5;
        $this->perUserGlobalLimit = is_numeric($perUserGlobalLimit) ? (int) $perUserGlobalLimit : 20;
        $this->windowSeconds = is_numeric($windowSeconds) ? (int) $windowSeconds : 3600;
    }

    /**
     * Check if user can run triage on a specific ticket.
     * Returns true if allowed, false if rate limited.
     */
    public function canRunTriage(User $user, Ticket $ticket): bool
    {
        return $this->check($user, $ticket);
    }

    /**
     * Check if user can generate a reply for a specific ticket.
     * Returns true if allowed, false if rate limited.
     */
    public function canGenerateReply(User $user, Ticket $ticket): bool
    {
        return $this->check($user, $ticket);
    }

    /**
     * Record an AI operation attempt for a user on a ticket.
     * Increments both per-ticket and global counters.
     */
    public function recordAttempt(User $user, Ticket $ticket): void
    {
        $perTicketKey = $this->getPerTicketKey($user, $ticket);
        $globalKey = $this->getGlobalKey($user);

        RateLimiter::hit($perTicketKey, $this->windowSeconds);
        RateLimiter::hit($globalKey, $this->windowSeconds);
    }

    /**
     * Get remaining attempts for per-ticket limit.
     * Returns 0 if already at limit.
     */
    public function getRemainingPerTicket(User $user, Ticket $ticket): int
    {
        $key = $this->getPerTicketKey($user, $ticket);
        /** @var int $attempts */
        $attempts = RateLimiter::attempts($key);

        return max(0, $this->perTicketLimit - $attempts);
    }

    /**
     * Get remaining attempts for global per-user limit.
     * Returns 0 if already at limit.
     */
    public function getRemainingGlobal(User $user): int
    {
        $key = $this->getGlobalKey($user);
        /** @var int $attempts */
        $attempts = RateLimiter::attempts($key);

        return max(0, $this->perUserGlobalLimit - $attempts);
    }

    /**
     * Get seconds until rate limit window resets for per-ticket limit.
     * Returns 0 if not rate limited.
     */
    public function getRetryAfterPerTicket(User $user, Ticket $ticket): int
    {
        $key = $this->getPerTicketKey($user, $ticket);

        return max(0, (int) RateLimiter::availableIn($key));
    }

    /**
     * Get seconds until rate limit window resets for global limit.
     * Returns 0 if not rate limited.
     */
    public function getRetryAfterGlobal(User $user): int
    {
        $key = $this->getGlobalKey($user);

        return max(0, (int) RateLimiter::availableIn($key));
    }

    /**
     * Reset rate limits for a user across all tickets.
     */
    public function resetForUser(User $user): void
    {
        $globalKey = $this->getGlobalKey($user);
        RateLimiter::clear($globalKey);
    }

    /**
     * Reset rate limits for a specific user-ticket pair.
     */
    public function resetForTicket(User $user, Ticket $ticket): void
    {
        $perTicketKey = $this->getPerTicketKey($user, $ticket);
        RateLimiter::clear($perTicketKey);
    }

    /**
     * Internal: Check both per-ticket and global limits.
     * Returns false if either limit is exceeded.
     */
    private function check(User $user, Ticket $ticket): bool
    {
        $perTicketKey = $this->getPerTicketKey($user, $ticket);
        $globalKey = $this->getGlobalKey($user);

        // Check per-ticket limit
        if (RateLimiter::tooManyAttempts($perTicketKey, $this->perTicketLimit)) {
            return false;
        }

        // Check global per-user limit
        return ! RateLimiter::tooManyAttempts($globalKey, $this->perUserGlobalLimit);
    }

    /**
     * Internal: Generate per-ticket rate limit key.
     */
    private function getPerTicketKey(User $user, Ticket $ticket): string
    {
        return sprintf('ai:triage:user:%s:ticket:%s', $user->id, $ticket->id);
    }

    /**
     * Internal: Generate global per-user rate limit key.
     */
    private function getGlobalKey(User $user): string
    {
        return 'ai:global:user:'.$user->id;
    }
}
