<?php

declare(strict_types=1);

use App\Enums\AiRunType;
use App\Enums\TicketMessageType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Livewire\Agent\AgentTicketDetail;
use App\Models\AiRun;
use App\Models\Category;
use App\Models\Macro;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('agent can view ticket detail page', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($agent)
        ->get(route('agent.tickets.show', $ticket))
        ->assertOk();
});

test('requester cannot access agent ticket detail', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($requester)
        ->get(route('agent.tickets.show', $ticket))
        ->assertForbidden();
});

test('agent ticket detail shows ticket subject', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create(['subject' => 'Important support request']);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Important support request');
});

test('agent can view public thread', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'type' => TicketMessageType::Public,
        'body' => 'Public thread message',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Public thread message');
});

test('agent can view internal notes', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'type' => TicketMessageType::Internal,
        'body' => 'Secret internal note',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Secret internal note');
});

test('public thread excludes ai drafts', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'type' => TicketMessageType::Public,
        'is_ai_draft' => true,
        'body' => 'AI draft should not appear',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertDontSee('AI draft should not appear');
});

test('agent can update ticket metadata', function (): void {
    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create(['is_active' => true]);
    $tag = Tag::factory()->create();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::New]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('status', TicketStatus::Triaged->value)
        ->set('priority', TicketPriority::High->value)
        ->set('categoryId', $category->id)
        ->set('selectedTagIds', [$tag->id])
        ->call('updateMetadata')
        ->assertHasNoErrors();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Triaged)
        ->and($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->category_id)->toBe($category->id)
        ->and($ticket->tags->pluck('id')->all())->toBe([$tag->id]);
});

test('agent can clear optional metadata fields', function (): void {
    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create();
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::Triaged,
        'priority' => TicketPriority::High,
        'category_id' => $category->id,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('priority', '')
        ->set('categoryId', '')
        ->call('updateMetadata')
        ->assertHasNoErrors();

    $ticket->refresh();
    expect($ticket->priority)->toBeNull()
        ->and($ticket->category_id)->toBeNull();
});

test('agent can assign ticket to another agent', function (): void {
    $agent = User::factory()->agent()->create();
    $otherAgent = User::factory()->agent()->create(['name' => 'Target Agent']);
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('assigneeId', $otherAgent->id)
        ->call('assignTicket')
        ->assertHasNoErrors();

    $ticket->refresh();
    expect($ticket->assigned_to_user_id)->toBe($otherAgent->id);
});

test('agent can unassign ticket', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->assignedTo($agent)->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('assigneeId', '')
        ->call('assignTicket')
        ->assertHasNoErrors();

    $ticket->refresh();
    expect($ticket->assigned_to_user_id)->toBeNull();
});

test('agent can assign ticket to self', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('assignToMe')
        ->assertHasNoErrors();

    $ticket->refresh();
    expect($ticket->assigned_to_user_id)->toBe($agent->id);
});

test('agent can send public reply', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'public')
        ->set('replyBody', 'This is a public agent reply')
        ->call('submitReply')
        ->assertHasNoErrors();

    $message = TicketMessage::query()
        ->where('ticket_id', $ticket->id)
        ->where('user_id', $agent->id)
        ->first();

    expect($message)->not->toBeNull()
        ->and($message->type)->toBe(TicketMessageType::Public)
        ->and($message->body)->toBe('This is a public agent reply');

    $ticket->refresh();
    expect($ticket->last_agent_message_at)->not->toBeNull();
});

test('agent can post internal note', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'internal')
        ->set('replyBody', 'This is an internal note')
        ->call('submitReply')
        ->assertHasNoErrors();

    $message = TicketMessage::query()
        ->where('ticket_id', $ticket->id)
        ->where('user_id', $agent->id)
        ->first();

    expect($message)->not->toBeNull()
        ->and($message->type)->toBe(TicketMessageType::Internal)
        ->and($message->body)->toBe('This is an internal note');

    $ticket->refresh();
    expect($ticket->last_agent_message_at)->toBeNull();
});

test('agent can reply with attachments', function (): void {
    Storage::fake('local');
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    $file = UploadedFile::fake()->create('report.pdf', 128, 'application/pdf');

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'See attached report')
        ->set('replyAttachments', [$file])
        ->call('submitReply')
        ->assertHasNoErrors();

    $attachment = TicketAttachment::query()->where('ticket_id', $ticket->id)->first();
    expect($attachment)->not->toBeNull()
        ->and($attachment->original_name)->toBe('report.pdf')
        ->and($attachment->disk)->toBe('local')
        ->and($attachment->uploaded_by_user_id)->toBe($agent->id);
});

test('reply validates required body', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', '')
        ->call('submitReply')
        ->assertHasErrors(['replyBody']);
});

test('requester cannot update ticket metadata', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('updateMetadata')
        ->assertForbidden();
});

test('requester cannot assign ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('assignTicket')
        ->assertForbidden();
});

test('requester cannot assign ticket to self', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('assignToMe')
        ->assertForbidden();
});

test('requester cannot post internal note', function (): void {
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Livewire::actingAs($requester)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'internal')
        ->set('replyBody', 'Internal note attempt')
        ->call('submitReply')
        ->assertForbidden();
});

test('ticket detail shows attachments', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'uploaded_by_user_id' => $agent->id,
        'original_name' => 'evidence.png',
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('evidence.png');
});

test('ticket detail shows escalation flag', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->urgent()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Escalation Required');
});

test('ticket detail shows ai triage placeholder', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('AI Triage')
        ->assertSee('AI triage has not run yet');
});

test('ticket detail shows ai summary when available', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create(['summary' => 'User reports billing overcharge on last invoice.']);

    AiRun::factory()->succeeded()->create([
        'ticket_id' => $ticket->id,
        'run_type' => AiRunType::Triage,
        'output_json' => [
            'summary' => 'User reports billing overcharge on last invoice.',
            'category_suggestion' => null,
            'priority_suggestion' => 'medium',
            'tags' => [],
            'clarifying_questions' => [],
            'escalation_required' => false,
        ],
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('User reports billing overcharge on last invoice.');
});

test('ticket detail shows categories and tags in metadata', function (): void {
    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create(['name' => 'Billing Support', 'is_active' => true]);
    $tag = Tag::factory()->create(['name' => 'VIP']);
    $ticket = Ticket::factory()->create(['category_id' => $category->id]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Billing Support')
        ->assertSee('VIP');
});

test('ticket detail loads initial metadata into properties', function (): void {
    $agent = User::factory()->agent()->create();
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::InProgress,
        'priority' => TicketPriority::Urgent,
        'category_id' => $category->id,
        'assigned_to_user_id' => $agent->id,
    ]);
    $ticket->tags()->attach($tag);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSet('status', TicketStatus::InProgress->value)
        ->assertSet('priority', TicketPriority::Urgent->value)
        ->assertSet('categoryId', $category->id)
        ->assertSet('assigneeId', $agent->id)
        ->assertSet('selectedTagIds', [$tag->id]);
});

test('submit reply resets form fields', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'internal')
        ->set('replyBody', 'A note')
        ->call('submitReply')
        ->assertSet('replyBody', '')
        ->assertSet('replyType', 'public');
});

test('agent detail shows agents in assignment dropdown', function (): void {
    $agent = User::factory()->agent()->create(['name' => 'Agent Smith']);
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('Agent Smith');
});

test('empty thread shows no messages placeholder', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSee('No public messages yet.')
        ->assertSee('No internal notes.');
});

test('agent can remove a selected reply attachment', function (): void {
    Storage::fake('local');
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    $file = UploadedFile::fake()->create('report.pdf', 128, 'application/pdf');

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyAttachments', [$file])
        ->assertCount('replyAttachments', 1)
        ->call('removeReplyAttachment', 0)
        ->assertCount('replyAttachments', 0);
});

test('agent can insert macro into empty reply textarea', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    $macro = Macro::factory()->create([
        'title' => 'Thank You',
        'body' => 'Thank you for contacting us. We appreciate your business.',
        'is_active' => true,
    ]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->assertSet('replyBody', '')
        ->set('selectedMacroId', $macro->id)
        ->call('insertMacro')
        ->assertSet('replyBody', 'Thank you for contacting us. We appreciate your business.')
        ->assertSet('selectedMacroId', '');
});

test('agent can insert macro into textarea with existing text', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    $macro = Macro::factory()->create([
        'title' => 'Signature',
        'body' => "Best regards,\nSupport Team",
        'is_active' => true,
    ]);

    $expectedBody = "Hello, I wanted to help with your issue.\n\nBest regards,\nSupport Team";

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Hello, I wanted to help with your issue.')
        ->set('selectedMacroId', $macro->id)
        ->call('insertMacro')
        ->assertSet('replyBody', $expectedBody)
        ->assertSet('selectedMacroId', '');
});

test('agent macro selector not shown for internal notes', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    Macro::factory()->create(['is_active' => true]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'internal')
        ->assertDontSee('Insert Macro');
});

test('agent sees macro selector only when macros exist', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'public')
        ->assertDontSee('Insert Macro');

    Macro::factory()->create(['is_active' => true]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyType', 'public')
        ->assertSee('Insert Macro');
});

test('insert macro does nothing when no macro selected', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();
    $macro = Macro::factory()->create(['is_active' => true]);

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->set('replyBody', 'Existing text')
        ->assertSet('selectedMacroId', '')
        ->call('insertMacro')
        ->assertSet('replyBody', 'Existing text')
        ->assertSet('selectedMacroId', '');
});

test('admin can delete ticket from agent detail', function (): void {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($admin)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('deleteTicket')
        ->assertRedirect(route('agent.tickets.index'));

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('agent cannot delete ticket from agent detail', function (): void {
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    Livewire::actingAs($agent)
        ->test(AgentTicketDetail::class, ['ticket' => $ticket])
        ->call('deleteTicket')
        ->assertForbidden();

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
});
