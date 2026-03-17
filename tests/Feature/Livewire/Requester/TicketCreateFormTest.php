<?php

declare(strict_types=1);

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\TicketMessageType;
use App\Enums\TicketStatus;
use App\Livewire\Requester\TicketCreateForm;
use App\Models\AiRun;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('requester can view create ticket page', function (): void {
    $requester = User::factory()->requester()->create();

    $this->actingAs($requester)
        ->get(route('requester.tickets.create'))
        ->assertOk();
});

test('requester can create a ticket', function (): void {
    $requester = User::factory()->requester()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->set('subject', 'Test ticket subject')
        ->set('description', 'This is a detailed description of the issue')
        ->set('categoryId', $category->id)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::query()->where('requester_id', $requester->id)->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket->subject)->toBe('Test ticket subject')
        ->and($ticket->description)->toBe('This is a detailed description of the issue')
        ->and($ticket->category_id)->toBe($category->id)
        ->and($ticket->status)->toBe(TicketStatus::New);

    expect(TicketMessage::query()->where('ticket_id', $ticket->id)->count())->toBe(1);

    $message = TicketMessage::query()->where('ticket_id', $ticket->id)->first();
    expect($message->type)->toBe(TicketMessageType::Public)
        ->and($message->body)->toBe('This is a detailed description of the issue');

    $aiRun = AiRun::query()->where('ticket_id', $ticket->id)->first();
    expect($aiRun)->not->toBeNull()
        ->and($aiRun->run_type)->toBe(AiRunType::Triage)
        ->and($aiRun->status)->toBe(AiRunStatus::Queued);
});

test('requester can create ticket without category', function (): void {
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->set('subject', 'No category ticket')
        ->set('description', 'Description without a category')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::query()->where('requester_id', $requester->id)->first();
    expect($ticket->category_id)->toBeNull();
});

test('requester can create ticket with attachments', function (): void {
    Storage::fake('local');
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->set('subject', 'Ticket with files')
        ->set('description', 'Description with attachment upload')
        ->set('attachments', [
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect();

    $ticket = Ticket::query()->where('requester_id', $requester->id)->first();
    expect(TicketAttachment::query()->where('ticket_id', $ticket->id)->count())->toBe(1);

    $attachment = TicketAttachment::query()->where('ticket_id', $ticket->id)->first();
    expect($attachment->original_name)->toBe('document.pdf')
        ->and($attachment->disk)->toBe('local');
});

test('ticket creation validates required fields', function (): void {
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->set('subject', '')
        ->set('description', '')
        ->call('submit')
        ->assertHasErrors(['subject', 'description']);
});

test('ticket creation validates minimum lengths', function (): void {
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->set('subject', 'abc')
        ->set('description', 'short')
        ->call('submit')
        ->assertHasErrors(['subject', 'description']);
});

test('create form shows active categories', function (): void {
    $active = Category::factory()->create(['name' => 'Active Category']);
    Category::factory()->create(['name' => 'Inactive Category', 'is_active' => false]);
    $requester = User::factory()->requester()->create();

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->assertSee('Active Category')
        ->assertDontSee('Inactive Category');
});

test('requester can remove an attachment before submitting', function (): void {
    Storage::fake('local');
    $requester = User::factory()->requester()->create();
    $file1 = UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf');
    $file2 = UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf');

    Livewire::actingAs($requester)
        ->test(TicketCreateForm::class)
        ->set('attachments', [$file1, $file2])
        ->assertCount('attachments', 2)
        ->call('removeAttachment', 0)
        ->assertCount('attachments', 1);
});
