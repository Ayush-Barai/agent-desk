<?php

declare(strict_types=1);

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('authorized requester can download own ticket attachment', function (): void {
    Storage::fake('local');
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Storage::disk('local')->put('attachments/test-file.pdf', 'file content');

    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'uploaded_by_user_id' => $requester->id,
        'storage_path' => 'attachments/test-file.pdf',
        'disk' => 'local',
        'original_name' => 'my-document.pdf',
        'mime_type' => 'application/pdf',
    ]);

    $this->actingAs($requester)
        ->get(route('attachments.download', $attachment))
        ->assertOk()
        ->assertDownload('my-document.pdf');
});

test('agent can download attachment from any ticket', function (): void {
    Storage::fake('local');
    $agent = User::factory()->agent()->create();
    $requester = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

    Storage::disk('local')->put('attachments/agent-file.pdf', 'file content');

    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'uploaded_by_user_id' => $requester->id,
        'storage_path' => 'attachments/agent-file.pdf',
        'disk' => 'local',
        'original_name' => 'report.pdf',
    ]);

    $this->actingAs($agent)
        ->get(route('attachments.download', $attachment))
        ->assertOk()
        ->assertDownload('report.pdf');
});

test('admin can download attachment from any ticket', function (): void {
    Storage::fake('local');
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    Storage::disk('local')->put('attachments/admin-file.pdf', 'file content');

    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'storage_path' => 'attachments/admin-file.pdf',
        'disk' => 'local',
        'original_name' => 'admin-report.pdf',
    ]);

    $this->actingAs($admin)
        ->get(route('attachments.download', $attachment))
        ->assertOk()
        ->assertDownload('admin-report.pdf');
});

test('requester cannot download attachment from other users ticket', function (): void {
    Storage::fake('local');
    $requester = User::factory()->requester()->create();
    $other = User::factory()->requester()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $other->id]);

    Storage::disk('local')->put('attachments/private.pdf', 'file content');

    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'uploaded_by_user_id' => $other->id,
        'storage_path' => 'attachments/private.pdf',
        'disk' => 'local',
        'original_name' => 'private.pdf',
    ]);

    $this->actingAs($requester)
        ->get(route('attachments.download', $attachment))
        ->assertForbidden();
});

test('unauthenticated user cannot download attachment', function (): void {
    $attachment = TicketAttachment::factory()->create();

    $this->get(route('attachments.download', $attachment))
        ->assertRedirect(route('login'));
});

test('download returns 404 when file is missing from storage', function (): void {
    Storage::fake('local');
    $agent = User::factory()->agent()->create();
    $ticket = Ticket::factory()->create();

    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'storage_path' => 'attachments/nonexistent.pdf',
        'disk' => 'local',
        'original_name' => 'ghost.pdf',
    ]);

    $this->actingAs($agent)
        ->get(route('attachments.download', $attachment))
        ->assertNotFound();
});

test('download url does not expose storage path', function (): void {
    $attachment = TicketAttachment::factory()->create([
        'storage_path' => 'ticket-attachments/secret-uuid/file.pdf',
    ]);

    $url = route('attachments.download', $attachment);

    expect($url)->not->toContain('ticket-attachments')
        ->and($url)->not->toContain('secret-uuid')
        ->and($url)->toContain($attachment->id);
});
