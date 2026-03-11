<?php

declare(strict_types=1);

namespace App\Livewire\Requester;

use App\Enums\TicketMessageType;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class TicketDetail extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $ticketId;

    #[Validate('required|string|min:1|max:10000')]
    public string $replyBody = '';

    /** @var array<int, TemporaryUploadedFile> */
    #[Validate(['replyAttachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,gif,txt,doc,docx,csv,zip'])]
    public array $replyAttachments = [];

    public function mount(Ticket $ticket): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('view', $ticket), 403);

        $this->ticketId = $ticket->id;
    }

    public function getTicket(): Ticket
    {
        return Ticket::with(['category', 'assignee', 'requester'])->findOrFail($this->ticketId);
    }

    /**
     * @return Collection<int, TicketMessage>
     */
    public function getThreadMessages(): Collection
    {
        return TicketMessage::query()->where('ticket_id', $this->ticketId)
            ->where('type', TicketMessageType::Public)
            ->where('is_ai_draft', false)
            ->with(['author'])
            ->oldest()
            ->get();
    }

    /**
     * @return Collection<int, TicketAttachment>
     */
    public function getAttachments(): Collection
    {
        return TicketAttachment::query()->where('ticket_id', $this->ticketId)
            ->with(['uploader'])
            ->oldest()
            ->get();
    }

    public function submitReply(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('createPublic', [TicketMessage::class, $ticket]), 403);

        $message = TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => TicketMessageType::Public,
            'body' => $this->replyBody,
        ]);

        foreach ($this->replyAttachments as $attachment) {
            /** @var TemporaryUploadedFile $attachment */
            $path = $attachment->store('ticket-attachments/'.$ticket->id, 'local');

            TicketAttachment::query()->create([
                'ticket_id' => $ticket->id,
                'ticket_message_id' => $message->id,
                'uploaded_by_user_id' => $user->id,
                'storage_path' => (string) $path,
                'disk' => 'local',
                'original_name' => $attachment->getClientOriginalName(),
                'mime_type' => (string) $attachment->getMimeType(),
                'size_bytes' => $attachment->getSize(),
            ]);
        }

        $ticket->update(['last_requester_message_at' => now()]);

        $this->reset('replyBody', 'replyAttachments');
    }

    public function render(): View
    {
        return view('livewire.requester.ticket-detail', [
            'ticket' => $this->getTicket(),
            'threadMessages' => $this->getThreadMessages(),
            'attachments' => $this->getAttachments(),
        ]);
    }
}
