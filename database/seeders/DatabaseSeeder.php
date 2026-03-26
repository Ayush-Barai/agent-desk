<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\TicketMessageType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\AiRun;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use App\Models\Macro;
use App\Models\SupportTargetConfig;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(['email' => 'admin@agentdesk.test'], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        User::query()->firstOrCreate(['email' => 'agent@agentdesk.test'], [
            'name' => 'Agent User',
            'password' => Hash::make('password'),
            'role' => UserRole::Agent,
            'email_verified_at' => now(),
        ]);

        User::query()->firstOrCreate(['email' => 'requester@agentdesk.test'], [
            'name' => 'Requester User',
            'password' => Hash::make('password'),
            'role' => UserRole::Requester,
            'email_verified_at' => now(),
        ]);

        Category::query()->firstOrCreate(['name' => 'Billing']);
        Category::query()->firstOrCreate(['name' => 'Technical Support']);
        Category::query()->firstOrCreate(['name' => 'General Inquiry']);

        Tag::query()->firstOrCreate(['name' => 'bug'], ['color' => '#ef4444']);
        Tag::query()->firstOrCreate(['name' => 'feature-request'], ['color' => '#3b82f6']);
        Tag::query()->firstOrCreate(['name' => 'urgent'], ['color' => '#f59e0b']);

        Macro::query()->firstOrCreate(['title' => 'Greeting'], [
            'body' => 'Hello! Thank you for reaching out to AgentDesk. How can we help you today?',
        ]);

        Macro::query()->firstOrCreate(['title' => 'Closing - Resolved'], [
            'body' => 'I am glad I could help! I will go ahead and mark this ticket as resolved. Please feel free to reach out if you have any other questions.',
        ]);

        Macro::query()->firstOrCreate(['title' => 'Request More Information'], [
            'body' => 'To better assist you, could you please provide more details about the issue? Any screenshots or error messages would be very helpful.',
        ]);

        Macro::query()->firstOrCreate(['title' => 'Working on it'], [
            'body' => 'I am currently investigating your request. I will provide an update as soon as I have more information.',
        ]);

        Macro::query()->firstOrCreate(['title' => 'Billing Clarification'], [
            'body' => 'I have reviewed your account and can confirm that the invoice charges are for your monthly subscription. Let me know if you need further clarification.',
        ]);

        if (SupportTargetConfig::query()->doesntExist()) {
            SupportTargetConfig::factory()->create();
        }

        KnowledgeBaseArticle::query()->firstOrCreate(['slug' => 'getting-started'], [
            'title' => 'Getting Started',
            'body' => 'Welcome to AgentDesk. This guide will help you get started with our platform.',
        ]);

        KnowledgeBaseArticle::query()->firstOrCreate(['slug' => 'password-reset'], [
            'title' => 'Password Reset',
            'body' => 'To reset your password, click the "Forgot Password" link on the login page.',
        ]);

        // Seed temporary tickets
        $requester = User::query()->where('email', 'requester@agentdesk.test')->firstOrFail();
        $agent = User::query()->where('email', 'agent@agentdesk.test')->firstOrFail();
        $targetConfig = SupportTargetConfig::query()->firstOrFail();

        $billingCategory = Category::query()->where('name', 'Billing')->first();
        $techCategory = Category::query()->where('name', 'Technical Support')->first();

        Tag::query()->where('name', 'urgent')->first();
        $bugTag = Tag::query()->where('name', 'bug')->first();

        // Ticket 1: New billing inquiry
        $ticket1 = Ticket::query()->create([
            'requester_id' => $requester->id,
            'category_id' => $billingCategory?->id,
            'subject' => 'Issue with my billing statement',
            'description' => 'I received an invoice that shows charges I do not recognize. Can you please check my account and explain these charges?',
            'status' => TicketStatus::New,
            'last_requester_message_at' => now(),
            'first_response_due_at' => now()->addHours((int) $targetConfig->first_response_hours),
            'resolution_due_at' => now()->addHours((int) $targetConfig->resolution_hours),
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket1->id,
            'user_id' => $requester->id,
            'type' => TicketMessageType::Public,
            'body' => 'I received an invoice that shows charges I do not recognize. Can you please check my account and explain these charges?',
        ]);

        AiRun::query()->create([
            'ticket_id' => $ticket1->id,
            'initiated_by_user_id' => $requester->id,
            'run_type' => AiRunType::Triage,
            'status' => AiRunStatus::Queued,
            'input_hash' => hash('sha256', 'Issue with my billing statement'.json_encode(['category' => 'Billing'])),
            'input_json' => [
                'subject' => 'Issue with my billing statement',
                'description' => 'Unrecognized charges on invoice',
            ],
        ]);

        AuditLog::query()->create([
            'actor_user_id' => $requester->id,
            'ticket_id' => $ticket1->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket1->id,
            'action' => 'ticket_created',
            'meta_json' => ['subject' => $ticket1->subject],
            'created_at' => $ticket1->created_at,
        ]);

        // Ticket 2: Triaged technical support issue
        $ticket2 = Ticket::query()->create([
            'requester_id' => $requester->id,
            'assigned_to_user_id' => $agent->id,
            'category_id' => $techCategory?->id,
            'subject' => 'Cannot login to account',
            'description' => "I keep getting an error message saying invalid credentials even though I'm entering the correct password. I have tried resetting my password but it didn't help.",
            'status' => TicketStatus::Triaged,
            'priority' => TicketPriority::High,
            'escalation_required' => true,
            'last_requester_message_at' => now()->subHours(2),
            'triaged_at' => now()->subHours(1),
            'first_response_due_at' => now()->addHours((int) $targetConfig->first_response_hours),
            'resolution_due_at' => now()->addHours((int) $targetConfig->resolution_hours),
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket2->id,
            'user_id' => $requester->id,
            'type' => TicketMessageType::Public,
            'body' => "I keep getting an error message saying invalid credentials even though I'm entering the correct password. I have tried resetting my password but it didn't help.",
        ]);

        $ticket2->tags()->attach($bugTag?->id);

        AiRun::query()->create([
            'ticket_id' => $ticket2->id,
            'initiated_by_user_id' => $agent->id,
            'run_type' => AiRunType::Triage,
            'status' => AiRunStatus::Succeeded,
            'input_hash' => hash('sha256', 'Cannot login to account'.json_encode(['category' => 'Technical Support'])),
            'output_json' => [
                'category_suggestion' => 'Technical Support',
                'priority_suggestion' => 'High',
                'summary' => 'User unable to log in despite correct credentials',
                'tags' => ['bug'],
                'escalation_flag' => true,
                'clarifying_questions' => ['Is this a new account?', 'Have you successfully logged in before?'],
            ],
            'completed_at' => now()->subHours(1),
        ]);

        AuditLog::query()->create([
            'actor_user_id' => $requester->id,
            'ticket_id' => $ticket2->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket2->id,
            'action' => 'ticket_created',
            'created_at' => $ticket2->created_at->subMinutes(30),
        ]);

        AuditLog::query()->create([
            'actor_user_id' => $agent->id,
            'ticket_id' => $ticket2->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket2->id,
            'action' => 'assignment_changed',
            'old_values_json' => ['assigned_to_user_id' => null],
            'new_values_json' => ['assigned_to_user_id' => $agent->id],
            'created_at' => $ticket2->created_at->subMinutes(20),
        ]);

        AuditLog::query()->create([
            'actor_user_id' => $agent->id,
            'ticket_id' => $ticket2->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket2->id,
            'action' => 'status_changed',
            'old_values_json' => ['status' => TicketStatus::New->value],
            'new_values_json' => ['status' => TicketStatus::Triaged->value],
            'created_at' => $ticket2->triaged_at,
        ]);

        // Ticket 3: In Progress ticket
        $ticket3 = Ticket::query()->create([
            'requester_id' => $requester->id,
            'assigned_to_user_id' => $agent->id,
            'category_id' => $techCategory?->id,
            'subject' => 'Feature request: Dark mode',
            'description' => 'It would be great if the application had a dark mode option. My eyes get tired when using the app for long periods at night.',
            'status' => TicketStatus::InProgress,
            'priority' => TicketPriority::Low,
            'last_requester_message_at' => now()->subDays(1),
            'last_agent_message_at' => now()->subHours(3),
            'triaged_at' => now()->subDays(1),
            'first_response_due_at' => now()->addDays(2),
            'resolution_due_at' => now()->addDays(5),
            'first_responded_at' => now()->subHours(24),
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket3->id,
            'user_id' => $requester->id,
            'type' => TicketMessageType::Public,
            'body' => 'It would be great if the application had a dark mode option. My eyes get tired when using the app for long periods at night.',
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket3->id,
            'user_id' => $agent->id,
            'type' => TicketMessageType::Public,
            'body' => "Thank you for the suggestion! Dark mode is a popular feature request and we're considering it for a future release. We'll keep you updated.",
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket3->id,
            'user_id' => $agent->id,
            'type' => TicketMessageType::Internal,
            'body' => 'Add to feature backlog and discuss with product team in next planning session.',
        ]);

        $ticket3->tags()->attach($bugTag?->id);

        AuditLog::query()->create([
            'actor_user_id' => $agent->id,
            'ticket_id' => $ticket3->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket3->id,
            'action' => 'status_changed',
            'old_values_json' => ['status' => TicketStatus::New->value],
            'new_values_json' => ['status' => TicketStatus::InProgress->value],
            'created_at' => $ticket3->first_responded_at,
        ]);

        // Ticket 4: Resolved ticket
        $ticket4 = Ticket::query()->create([
            'requester_id' => $requester->id,
            'assigned_to_user_id' => $agent->id,
            'category_id' => $billingCategory?->id,
            'subject' => 'Subscription cancellation confirmation',
            'description' => 'I would like to cancel my subscription effective immediately. Please confirm the cancellation and let me know about any refunds.',
            'status' => TicketStatus::Resolved,
            'priority' => TicketPriority::Medium,
            'last_requester_message_at' => now()->subDays(3),
            'last_agent_message_at' => now()->subDays(2),
            'triaged_at' => now()->subDays(3),
            'first_response_due_at' => now()->subDays(2),
            'resolution_due_at' => now()->subDays(1),
            'first_responded_at' => now()->subDays(2),
            'resolved_at' => now()->subDays(2),
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket4->id,
            'user_id' => $requester->id,
            'type' => TicketMessageType::Public,
            'body' => 'I would like to cancel my subscription effective immediately. Please confirm the cancellation and let me know about any refunds.',
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket4->id,
            'user_id' => $agent->id,
            'type' => TicketMessageType::Public,
            'body' => 'Your subscription has been successfully cancelled as of today. You will receive a refund of $29.99 to your original payment method within 3-5 business days. Thank you for being a customer!',
        ]);

        AuditLog::query()->create([
            'actor_user_id' => $agent->id,
            'ticket_id' => $ticket4->id,
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket4->id,
            'action' => 'status_changed',
            'old_values_json' => ['status' => TicketStatus::InProgress->value],
            'new_values_json' => ['status' => TicketStatus::Resolved->value],
            'created_at' => $ticket4->resolved_at,
        ]);

        /** @var Category|null $generalCategory */
        $generalCategory = Category::query()->where('name', 'General Inquiry')->first();

        // Create 10 additional agents
        User::factory()->count(10)->agent()->create();

        // Create 50 additional requesters and random tickets for them
        User::factory()->count(50)->requester()->create()->each(function (User $requester) use ($techCategory, $billingCategory, $generalCategory, $targetConfig): void {
            // 60% chance to have 1-3 tickets
            if (fake()->boolean(60)) {
                $count = fake()->numberBetween(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    $status = fake()->randomElement(TicketStatus::cases());
                    $priority = fake()->randomElement(TicketPriority::cases());
                    /** @var Category|null $category */
                    $category = fake()->randomElement([$techCategory, $billingCategory, $generalCategory]);

                    $assignedAgent = null;
                    if ($status !== TicketStatus::New) {
                        $assignedAgent = User::query()->where('role', UserRole::Agent)->inRandomOrder()->first();
                    }

                    $ticket = Ticket::query()->create([
                        'requester_id' => $requester->id,
                        'assigned_to_user_id' => $assignedAgent?->id,
                        'category_id' => $category?->id,
                        'status' => $status,
                        'priority' => $priority,
                        'subject' => fake()->sentence(),
                        'description' => fake()->paragraph(),
                        'last_requester_message_at' => now()->subHours(fake()->numberBetween(1, 72)),
                        'first_response_due_at' => now()->addHours((int) $targetConfig->first_response_hours),
                        'resolution_due_at' => now()->addHours((int) $targetConfig->resolution_hours),
                    ]);

                    // Initial message
                    TicketMessage::query()->create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $requester->id,
                        'type' => TicketMessageType::Public,
                        'body' => $ticket->description,
                    ]);

                    // If not new, add an agent reply
                    if ($status !== TicketStatus::New && $assignedAgent) {
                        TicketMessage::query()->create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $assignedAgent->id,
                            'type' => TicketMessageType::Public,
                            'body' => fake()->paragraph(),
                        ]);
                        $ticket->update(['last_agent_message_at' => now()]);
                    }
                }
            }
        });
    }
}
