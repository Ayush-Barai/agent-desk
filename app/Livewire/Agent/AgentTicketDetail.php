<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Enums\TicketMessageType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class AgentTicketDetail extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $ticketId;

    public string $status = '';

    public string $priority = '';

    public string $categoryId = '';

    public string $assigneeId = '';

    /** @var array<int, string> */
    public array $selectedTagIds = [];

    #[Validate('required|string|min:1|max:10000')]
    public string $replyBody = '';

    public string $replyType = 'public';

    /** @var array<int, TemporaryUploadedFile> */
    #[Validate(['replyAttachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,gif,txt,doc,docx,csv,zip'])]
    public array $replyAttachments = [];

    public function mount(Ticket $ticket): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can('view', $ticket), 403);

        $this->ticketId = $ticket->id;
        $this->status = $ticket->status->value;
        $this->priority = $ticket->priority !== null ? $ticket->priority->value : '';
        $this->categoryId = $ticket->category_id ?? '';
        $this->assigneeId = $ticket->assigned_to_user_id ?? '';
        /** @var array<int, string> $tagIds */
        $tagIds = $ticket->tags()->pluck('tags.id')->all();
        $this->selectedTagIds = $tagIds;
    }

    public function getTicket(): Ticket
    {
        return Ticket::query()
            ->with(['category', 'assignee', 'requester', 'tags'])
            ->findOrFail($this->ticketId);
    }

    /**
     * @return Collection<int, TicketMessage>
     */
    public function getPublicThread(): Collection
    {
        return TicketMessage::query()
            ->where('ticket_id', $this->ticketId)
            ->where('type', TicketMessageType::Public)
            ->where('is_ai_draft', false)
            ->with(['author'])
            ->oldest()
            ->get();
    }

    /**
     * @return Collection<int, TicketMessage>
     */
    public function getInternalNotes(): Collection
    {
        return TicketMessage::query()
            ->where('ticket_id', $this->ticketId)
            ->where('type', TicketMessageType::Internal)
            ->with(['author'])
            ->oldest()
            ->get();
    }

    /**
     * @return Collection<int, TicketAttachment>
     */
    public function getAttachments(): Collection
    {
        return TicketAttachment::query()
            ->where('ticket_id', $this->ticketId)
            ->with(['uploader'])
            ->oldest()
            ->get();
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return Category::query()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getAllTags(): Collection
    {
        return Tag::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function getAgents(): Collection
    {
        return User::query()
            ->where(function (Builder $q): void {
                $q->where('role', 'agent')
                    ->orWhere('role', 'admin');
            })
            ->orderBy('name')
            ->get();
    }

    public function updateMetadata(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('update', $ticket), 403);

        $ticket->update([
            'status' => $this->status,
            'priority' => $this->priority !== '' ? $this->priority : null,
            'category_id' => $this->categoryId !== '' ? $this->categoryId : null,
        ]);

        $ticket->tags()->sync($this->selectedTagIds);
    }

    public function assignTicket(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('assign', $ticket), 403);

        $ticket->update([
            'assigned_to_user_id' => $this->assigneeId !== '' ? $this->assigneeId : null,
        ]);
    }

    public function assignToMe(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('assign', $ticket), 403);

        $ticket->update(['assigned_to_user_id' => $user->id]);
        $this->assigneeId = $user->id;
    }

    public function submitReply(): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        $messageType = $this->replyType === 'internal'
            ? TicketMessageType::Internal
            : TicketMessageType::Public;

        if ($messageType === TicketMessageType::Internal) {
            abort_unless($user->can('createInternal', [TicketMessage::class, $ticket]), 403);
        } else {
            abort_unless($user->can('createPublic', [TicketMessage::class, $ticket]), 403);
        }

        $message = TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => $messageType,
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

        if ($messageType === TicketMessageType::Public) {
            $ticket->update(['last_agent_message_at' => now()]);
        }

        $this->reset('replyBody', 'replyAttachments');
        $this->replyType = 'public';
    }

    /**
     * @return list<TicketStatus>
     */
    public function getStatuses(): array
    {
        return TicketStatus::cases();
    }

    /**
     * @return list<TicketPriority>
     */
    public function getPriorities(): array
    {
        return TicketPriority::cases();
    }

    public function render(): View
    {
        return view('livewire.agent.agent-ticket-detail', [
            'ticket' => $this->getTicket(),
            'publicThread' => $this->getPublicThread(),
            'internalNotes' => $this->getInternalNotes(),
            'attachments' => $this->getAttachments(),
            'categories' => $this->getCategories(),
            'allTags' => $this->getAllTags(),
            'agents' => $this->getAgents(),
            'statuses' => $this->getStatuses(),
            'priorities' => $this->getPriorities(),
        ]);
    }
}
