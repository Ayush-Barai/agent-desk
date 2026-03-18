<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Services\AiRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AiRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    private AiRateLimiter $rateLimiter;

    private User $user;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rateLimiter = new AiRateLimiter();
        $this->user = User::factory()->create();
        $this->ticket = Ticket::factory()->create();
    }

    public function test_can_run_triage_returns_true_initially(): void
    {
        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $this->ticket));
    }

    public function test_can_generate_reply_returns_true_initially(): void
    {
        $this->assertTrue($this->rateLimiter->canGenerateReply($this->user, $this->ticket));
    }

    public function test_record_attempt_increments_counter(): void
    {
        $this->rateLimiter->recordAttempt($this->user, $this->ticket);

        $remaining = $this->rateLimiter->getRemainingPerTicket($this->user, $this->ticket);
        $defaultLimit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);

        $this->assertSame($defaultLimit - 1, $remaining);
    }

    public function test_per_ticket_limit_enforced(): void
    {
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);

        // Record attempts up to the limit
        for ($i = 0; $i < $limit; $i++) {
            $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $this->ticket));
            $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        }

        // Next attempt should be blocked
        $this->assertFalse($this->rateLimiter->canRunTriage($this->user, $this->ticket));
    }

    public function test_global_per_user_limit_enforced(): void
    {
        $limit = (int) config('ai.rate_limits.global_per_user_hour', 20);

        // Create multiple tickets
        $tickets = Ticket::factory()->count(5)->create();

        // Record attempts across tickets up to the global limit
        for ($i = 0; $i < $limit; $i++) {
            $ticket = $tickets[$i % 5];
            $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $ticket));
            $this->rateLimiter->recordAttempt($this->user, $ticket);
        }

        // Next attempt on any ticket should be blocked by global limit
        $this->assertFalse($this->rateLimiter->canRunTriage($this->user, $tickets[0]));
    }

    public function test_get_remaining_per_ticket(): void
    {
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);

        $this->assertSame($limit, $this->rateLimiter->getRemainingPerTicket($this->user, $this->ticket));

        $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        $this->assertSame($limit - 1, $this->rateLimiter->getRemainingPerTicket($this->user, $this->ticket));

        $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        $this->assertSame($limit - 2, $this->rateLimiter->getRemainingPerTicket($this->user, $this->ticket));
    }

    public function test_get_remaining_global(): void
    {
        $limit = (int) config('ai.rate_limits.global_per_user_hour', 20);

        $this->assertSame($limit, $this->rateLimiter->getRemainingGlobal($this->user));

        $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        $this->assertSame($limit - 1, $this->rateLimiter->getRemainingGlobal($this->user));
    }

    public function test_get_retry_after_when_not_limited(): void
    {
        $this->assertSame(0, $this->rateLimiter->getRetryAfterPerTicket($this->user, $this->ticket));
        $this->assertSame(0, $this->rateLimiter->getRetryAfterGlobal($this->user));
    }

    public function test_get_retry_after_when_limited(): void
    {
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);

        // Hit the per-ticket limit
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        }

        // Should have retry_after > 0
        $retryAfter = $this->rateLimiter->getRetryAfterPerTicket($this->user, $this->ticket);
        $this->assertGreaterThan(0, $retryAfter);
        $this->assertLessThanOrEqual((int) config('ai.rate_limits.window_seconds', 3600), $retryAfter);
    }

    public function test_reset_for_user(): void
    {
        $limit = (int) config('ai.rate_limits.global_per_user_hour', 20);

        // Hit the global limit with multiple tickets to avoid per-ticket limit
        $tickets = Ticket::factory()->count(6)->create();
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->user, $tickets[$i % 6]);
        }

        $this->assertFalse($this->rateLimiter->canRunTriage($this->user, $this->ticket));

        // Reset global
        $this->rateLimiter->resetForUser($this->user);
        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $this->ticket));
    }

    public function test_reset_for_ticket(): void
    {
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);

        // Hit the per-ticket limit
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        }

        $this->assertFalse($this->rateLimiter->canRunTriage($this->user, $this->ticket));

        // Reset
        $this->rateLimiter->resetForTicket($this->user, $this->ticket);
        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $this->ticket));
    }

    public function test_different_users_have_separate_limits(): void
    {
        $user2 = User::factory()->create();

        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $this->ticket));
        $this->assertTrue($this->rateLimiter->canRunTriage($user2, $this->ticket));

        // Hit limit for user1
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        }

        // User1 should be limited, but user2 should not
        $this->assertFalse($this->rateLimiter->canRunTriage($this->user, $this->ticket));
        $this->assertTrue($this->rateLimiter->canRunTriage($user2, $this->ticket));
    }

    public function test_different_tickets_have_separate_limits(): void
    {
        $ticket2 = Ticket::factory()->create();

        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $this->ticket));
        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $ticket2));

        // Hit limit for ticket1
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->user, $this->ticket);
        }

        // Ticket1 should be limited, but ticket2 should not
        $this->assertFalse($this->rateLimiter->canRunTriage($this->user, $this->ticket));
        $this->assertTrue($this->rateLimiter->canRunTriage($this->user, $ticket2));
    }
}
