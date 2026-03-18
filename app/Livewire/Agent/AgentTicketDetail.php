<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Actions\CreateAuditLog;
use App\DTOs\ReplyDraftInput;
use App\DTOs\TriageInput;
use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\TicketMessageType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Jobs\DraftTicketReplyJob;
use App\Jobs\RunTicketTriageJob;
use App\Models\AiRun;
use App\Models\Category;
use App\Models\Macro;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketResolvedNotification;
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

    public string $selectedMacroId = '';

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
                $q->where('role', 'agent');
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

        $audit = new CreateAuditLog();

        $oldStatus = $ticket->status->value;
        $newStatus = $this->status;
        $oldPriority = $ticket->priority !== null ? $ticket->priority->value : '';
        $newPriority = $this->priority;
        $oldCategoryId = $ticket->category_id ?? '';
        $newCategoryId = $this->categoryId;

        $ticket->update([
            'status' => $this->status,
            'priority' => $this->priority !== '' ? $this->priority : null,
            'category_id' => $this->categoryId !== '' ? $this->categoryId : null,
        ]);

        if ($oldStatus !== $newStatus) {
            $audit->execute(
                action: 'status_changed',
                actor: $user,
                ticketId: $ticket->id,
                auditable: $ticket,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $newStatus],
            );

            if ($newStatus === TicketStatus::Resolved->value && $ticket->requester !== null) {
                $ticket->requester->notify(new TicketResolvedNotification($ticket));
            }
        }

        if ($oldPriority !== $newPriority) {
            $audit->execute(
                action: 'priority_changed',
                actor: $user,
                ticketId: $ticket->id,
                auditable: $ticket,
                oldValues: ['priority' => $oldPriority],
                newValues: ['priority' => $newPriority],
            );
        }

        if ($oldCategoryId !== $newCategoryId) {
            $audit->execute(
                action: 'category_changed',
                actor: $user,
                ticketId: $ticket->id,
                auditable: $ticket,
                oldValues: ['category_id' => $oldCategoryId],
                newValues: ['category_id' => $newCategoryId],
            );
        }

        $ticket->tags()->sync($this->selectedTagIds);
    }

    public function assignTicket(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('assign', $ticket), 403);

        $oldAssigneeId = $ticket->assigned_to_user_id ?? '';
        $newAssigneeId = $this->assigneeId;

        $ticket->update([
            'assigned_to_user_id' => $this->assigneeId !== '' ? $this->assigneeId : null,
        ]);

        if ($oldAssigneeId !== $newAssigneeId) {
            new CreateAuditLog()->execute(
                action: 'assignment_changed',
                actor: $user,
                ticketId: $ticket->id,
                auditable: $ticket,
                oldValues: ['assigned_to_user_id' => $oldAssigneeId],
                newValues: ['assigned_to_user_id' => $newAssigneeId],
            );

            if ($newAssigneeId !== '') {
                $assignee = User::query()->findOrFail($newAssigneeId);
                $assignee->notify(new TicketAssignedNotification($ticket, $user));
            }
        }
    }

    public function assignToMe(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('assign', $ticket), 403);

        $oldAssigneeId = $ticket->assigned_to_user_id ?? '';

        $ticket->update(['assigned_to_user_id' => $user->id]);
        $this->assigneeId = $user->id;

        if ($oldAssigneeId !== $user->id) {
            new CreateAuditLog()->execute(
                action: 'assignment_changed',
                actor: $user,
                ticketId: $ticket->id,
                auditable: $ticket,
                oldValues: ['assigned_to_user_id' => $oldAssigneeId],
                newValues: ['assigned_to_user_id' => $user->id],
            );

            $user->notify(new TicketAssignedNotification($ticket, $user));
        }
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
            $updateData = ['last_agent_message_at' => now()];

            if ($ticket->first_responded_at === null) {
                $updateData['first_responded_at'] = now();
            }

            $ticket->update($updateData);
        }

        $this->reset('replyBody', 'replyAttachments', 'replyType');
    }

    public function removeReplyAttachment(int $index): void
    {
        if (isset($this->replyAttachments[$index])) {
            unset($this->replyAttachments[$index]);
            $this->replyAttachments = array_values($this->replyAttachments);
        }
    }

    public function runAiTriage(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()->findOrFail($this->ticketId);

        abort_unless($user->can('create', AiRun::class), 403);

        $input = new TriageInput(
            ticketId: $ticket->id,
            subject: $ticket->subject,
            description: $ticket->description,
        );

        $inputHash = hash('sha256', json_encode([
            'ticket_id' => $input->ticketId,
            'subject' => $input->subject,
            'description' => $input->description,
        ], JSON_THROW_ON_ERROR));

        $existingRun = AiRun::query()
            ->where('ticket_id', $ticket->id)
            ->where('run_type', AiRunType::Triage)
            ->where('input_hash', $inputHash)
            ->where('status', AiRunStatus::Succeeded)
            ->first();

        if ($existingRun instanceof AiRun) {
            $this->applyTriageResult($existingRun);

            return;
        }

        $aiRun = AiRun::query()->create([
            'ticket_id' => $ticket->id,
            'initiated_by_user_id' => $user->id,
            'run_type' => AiRunType::Triage,
            'status' => AiRunStatus::Queued,
            'input_hash' => $inputHash,
            'input_json' => [
                'ticket_id' => $input->ticketId,
                'subject' => $input->subject,
                'description' => $input->description,
            ],
        ]);

        dispatch(new RunTicketTriageJob($aiRun->id, $input));
    }

    public function applyTriageResult(AiRun $aiRun): void
    {
        /** @var array{category_suggestion?: string|null, priority_suggestion?: string|null, summary?: string, tags?: list<string>, clarifying_questions?: list<string>, escalation_required?: bool}|null $output */
        $output = $aiRun->output_json;

        if ($output === null) {
            return;
        }

        $categorySuggestion = $output['category_suggestion'] ?? null;
        if ($categorySuggestion !== null) {
            $category = Category::query()
                ->where('name', $categorySuggestion)
                ->where('is_active', true)
                ->first();
            if ($category instanceof Category) {
                $this->categoryId = $category->id;
            }
        }

        $prioritySuggestion = $output['priority_suggestion'] ?? null;
        if ($prioritySuggestion !== null) {
            $priority = TicketPriority::tryFrom($prioritySuggestion);
            if ($priority !== null) {
                $this->priority = $priority->value;
            }
        }

        $tags = $output['tags'] ?? [];
        if ($tags !== []) {
            $tagIds = Tag::query()
                ->whereIn('name', $tags)
                ->pluck('id')
                ->all();
            /** @var array<int, string> $tagIds */
            $this->selectedTagIds = $tagIds;
        }
    }

    public function getLatestTriageRun(): ?AiRun
    {
        return AiRun::query()
            ->where('ticket_id', $this->ticketId)
            ->where('run_type', AiRunType::Triage)
            ->latest()
            ->first();
    }

    public function generateReply(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $ticket = Ticket::query()
            ->with(['requester'])
            ->findOrFail($this->ticketId);

        abort_unless($user->can('create', AiRun::class), 403);

        /** @var list<array{role: string, body: string}> $messageHistory */
        $messageHistory = TicketMessage::query()
            ->where('ticket_id', $ticket->id)
            ->where('type', TicketMessageType::Public)
            ->where('is_ai_draft', false)
            ->with(['author'])
            ->oldest()
            ->get()
            ->map(fn (TicketMessage $m): array => [
                'role' => $m->user_id === $ticket->requester_id ? 'requester' : 'agent',
                'body' => $m->body,
            ])
            ->values()
            ->all();

        $input = new ReplyDraftInput(
            ticketId: $ticket->id,
            subject: $ticket->subject,
            description: $ticket->description,
            messageHistory: $messageHistory,
            kbSnippets: [],
        );

        $seedText = $this->replyBody;

        $inputHash = hash('sha256', json_encode([
            'ticket_id' => $input->ticketId,
            'subject' => $input->subject,
            'description' => $input->description,
            'message_count' => count($input->messageHistory),
            'seed_text' => $seedText,
        ], JSON_THROW_ON_ERROR));

        $aiRun = AiRun::query()->create([
            'ticket_id' => $ticket->id,
            'initiated_by_user_id' => $user->id,
            'run_type' => AiRunType::ReplyDraft,
            'status' => AiRunStatus::Queued,
            'progress_state' => 'Retrieving',
            'input_hash' => $inputHash,
            'input_json' => [
                'ticket_id' => $input->ticketId,
                'subject' => $input->subject,
                'description' => $input->description,
                'message_count' => count($input->messageHistory),
                'seed_text' => $seedText,
            ],
        ]);

        dispatch(new DraftTicketReplyJob($aiRun->id, $input, $seedText));
    }

    public function applyDraftReply(string $aiRunId): void
    {
        $aiRun = AiRun::query()->findOrFail($aiRunId);
        $output = $aiRun->output_json;

        if ($output === null) {
            return;
        }

        $draftReply = $output['draft_reply'] ?? '';
        if (is_string($draftReply) && $draftReply !== '') {
            $this->replyBody = $draftReply;
            $this->dispatch('draft-applied');
            $this->resetErrorBag('replyBody');
        }
    }

    public function getLatestReplyDraftRun(): ?AiRun
    {
        return AiRun::query()
            ->where('ticket_id', $this->ticketId)
            ->where('run_type', AiRunType::ReplyDraft)
            ->latest()
            ->first();
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

    /**
     * @return Collection<int, Macro>
     */
    public function getMacros(): Collection
    {
        return Macro::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    public function insertMacro(): void
    {
        if ($this->selectedMacroId === '') {
            return;
        }

        $macro = Macro::query()->findOrFail($this->selectedMacroId);

        if ($this->replyBody === '') {
            $this->replyBody = $macro->body;
        } else {
            $this->replyBody .= "\n\n".$macro->body;
        }

        $this->selectedMacroId = '';
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
            'macros' => $this->getMacros(),
            'latestTriageRun' => $this->getLatestTriageRun(),
            'latestReplyDraftRun' => $this->getLatestReplyDraftRun(),
        ]);
    }
}
