<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Ai\Agents\ReplyDraftAgent;
use App\DTOs\ReplyDraftInput;
use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\UserRole;
use App\Exceptions\AiRateLimitExceededException;
use App\Jobs\DraftTicketReplyJob;
use App\Livewire\Agent\AgentTicketDetail;
use App\Models\AiRun;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AiRateLimiter;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\TestCase;

final class AgentTicketDetailRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private Ticket $ticket;

    private AiRateLimiter $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['role' => UserRole::Agent]);
        $this->ticket = Ticket::factory()->create();
        $this->rateLimiter = new AiRateLimiter();

        // Reset rate limiter before each test
        $this->rateLimiter->resetForUser($this->agent);
        $this->rateLimiter->resetForTicket($this->agent, $this->ticket);
    }

    public function test_ai_triage_button_is_enabled_when_not_rate_limited(): void
    {
        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->assertSet('isRateLimited', false)
            ->assertSet('rateLimitError', null);
    }

    public function test_ai_triage_rate_limit_error_displayed_when_limit_hit(): void
    {
        // Hit the rate limit
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->agent, $this->ticket);
        }

        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage')
            ->assertSet('isRateLimited', true)
            ->assertSet('rateLimitType', 'triage')
            ->assertSee("You've reached the rate limit");
    }

    public function test_ai_reply_rate_limit_error_displayed_when_limit_hit(): void
    {
        // Hit the rate limit
        $limit = (int) config('ai.rate_limits.global_per_user_hour', 20);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->agent, $this->ticket);
        }

        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('generateReply')
            ->assertSet('isRateLimited', true)
            ->assertSet('rateLimitType', 'reply')
            ->assertSee("You've reached the rate limit");
    }

    public function test_rate_limit_error_can_be_cleared(): void
    {
        // Hit the rate limit
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->agent, $this->ticket);
        }

        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage')
            ->assertSet('isRateLimited', true)
            ->call('clearRateLimitError')
            ->assertSet('isRateLimited', false)
            ->assertSet('rateLimitError', null);
    }

    public function test_in_flight_duplicate_request_is_prevented(): void
    {
        // Create an in-flight triage request
        AiRun::factory()->create([
            'ticket_id' => $this->ticket->id,
            'initiated_by_user_id' => $this->agent->id,
            'run_type' => AiRunType::Triage,
            'status' => AiRunStatus::Queued,
            'input_hash' => hash('sha256', json_encode([
                'ticket_id' => $this->ticket->id,
                'subject' => $this->ticket->subject,
                'description' => $this->ticket->description,
            ], JSON_THROW_ON_ERROR)),
        ]);

        // Try to run triage again with same input
        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage');

        // Verify only one in-flight run exists
        $triageRuns = AiRun::query()->where('ticket_id', $this->ticket->id)
            ->where('run_type', AiRunType::Triage)
            ->whereIn('status', [AiRunStatus::Queued, AiRunStatus::Running])
            ->count();

        $this->assertSame(1, $triageRuns);
    }

    public function test_existing_successful_run_is_reused(): void
    {
        $inputHash = hash('sha256', json_encode([
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'description' => $this->ticket->description,
        ], JSON_THROW_ON_ERROR));

        $testCategoryName = 'Test Category';

        // Create a category first
        $category = Category::factory()->create(['name' => $testCategoryName, 'is_active' => true]);

        // Create a successful triage run
        AiRun::factory()->create([
            'ticket_id' => $this->ticket->id,
            'initiated_by_user_id' => $this->agent->id,
            'run_type' => AiRunType::Triage,
            'status' => AiRunStatus::Succeeded,
            'input_hash' => $inputHash,
            'output_json' => [
                'category_suggestion' => $testCategoryName,
                'priority_suggestion' => 'medium',
                'summary' => 'Test summary',
                'tags' => [],
                'clarifying_questions' => [],
                'escalation_required' => false,
            ],
        ]);

        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage')
            // Should apply the result from existing successful run - verify categoryId was set
            ->assertSet('categoryId', $category->id);
    }

    public function test_rate_limit_retry_after_shown_to_user(): void
    {
        // Hit the rate limit
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->agent, $this->ticket);
        }

        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage')
            ->assertSet('isRateLimited', true)
            // Just verify retry_after is a positive number (exact value depends on timing)
            ->assertSet('rateLimitRetryAfter', fn ($value): bool => is_int($value) && $value > 0);
    }

    public function test_remaining_attempts_shown_when_rate_limited(): void
    {
        // Record all but one attempt
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->agent, $this->ticket);
        }

        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage')
            ->assertSet('isRateLimited', true)
            ->assertSet('remainingAiAttempts', 0);
    }

    public function test_separate_tickets_have_separate_rate_limits(): void
    {
        $ticket2 = Ticket::factory()->create();
        $limit = (int) config('ai.rate_limits.triage_per_ticket_hour', 5);

        // Hit limit for ticket1
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->recordAttempt($this->agent, $this->ticket);
        }

        // ticket1 should be rate limited
        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $this->ticket])
            ->call('runAiTriage')
            ->assertSet('isRateLimited', true);

        // ticket2 should not be rate limited
        Livewire::actingAs($this->agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket2])
            ->call('runAiTriage')
            ->assertSet('isRateLimited', false);
    }

    public function test_ai_rate_limit_exceeded_exception_properties(): void
    {
        $exception = new AiRateLimitExceededException(
            throttleKey: 'user:1:ticket:1',
            limit: 10,
            retryAfter: 120,
            message: 'Custom error message'
        );

        $this->assertEquals('user:1:ticket:1', $exception->throttleKey);
        $this->assertEquals(10, $exception->limit);
        $this->assertEquals(120, $exception->retryAfter);
        $this->assertEquals('Custom error message', $exception->getMessage());
        $this->assertEquals('Rate limit hit. Try again in about 2 minute(s).', $exception->getUserMessage());
        $this->assertTrue($exception->isPerTicket());
        $this->assertFalse($exception->isGlobal());

        $globalException = new AiRateLimitExceededException(
            throttleKey: 'user:1:global',
            limit: 20,
            retryAfter: 30
        );
        $this->assertEquals('Rate limit hit. Try again in 30 seconds.', $globalException->getUserMessage());
        $this->assertTrue($globalException->isGlobal());
        $this->assertFalse($globalException->isPerTicket());
    }

    public function test_draft_reply_deduplication(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();

        // Create an existing in-flight run
        $input = new ReplyDraftInput(
            ticketId: $ticket->id,
            subject: $ticket->subject,
            description: $ticket->description,
            messageHistory: [],
            kbSnippets: []
        );

        $inputHash = hash('sha256', json_encode([
            'ticket_id' => $input->ticketId,
            'subject' => $input->subject,
            'description' => $input->description,
            'message_count' => count($input->messageHistory),
            'seed_text' => '',
        ], JSON_THROW_ON_ERROR));

        AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'run_type' => AiRunType::ReplyDraft,
            'input_hash' => $inputHash,
            'status' => AiRunStatus::Running,
        ]);

        Livewire::actingAs($agent)
            ->test(AgentTicketDetail::class, ['ticket' => $ticket])
            ->call('generateReply')
            ->assertDispatched('info', message: 'This reply draft request is already running. Refresh in a moment to see results.');
    }

    public function test_draft_ticket_reply_job_logs_rate_limit_error(): void
    {
        Log::shouldReceive('warning')
            ->once();

        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create();
        $aiRun = AiRun::factory()->create([
            'ticket_id' => $ticket->id,
            'initiated_by_user_id' => $agent->id,
            'run_type' => AiRunType::ReplyDraft,
        ]);

        $input = new ReplyDraftInput(
            ticketId: $ticket->id,
            subject: 'Test',
            description: 'Test',
            messageHistory: [],
            kbSnippets: []
        );

        // Resolve via container using a fake object context
        $fakeAgent = new class
        {
            public function prompt($prompt): never
            {
                throw new Exception('Rate limit hit');
            }

            public function getKbTool(): null
            {
                return null;
            }
        };

        $this->app->instance(ReplyDraftAgent::class, $fakeAgent);

        $job = new DraftTicketReplyJob($aiRun->id, $input);
        $job->handle();

        $aiRun->refresh();
        $this->assertEquals(AiRunStatus::Failed, $aiRun->status);
        $this->assertStringContainsString('RATE_LIMIT:', $aiRun->error_message);
    }
}
