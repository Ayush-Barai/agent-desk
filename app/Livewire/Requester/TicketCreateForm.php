<?php

declare(strict_types=1);

namespace App\Livewire\Requester;

use App\Enums\AiRunStatus;
use App\Enums\AiRunType;
use App\Enums\TicketMessageType;
use App\Enums\TicketStatus;
use App\Models\AiRun;
use App\Models\Category;
use App\Models\SupportTargetConfig;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class TicketCreateForm extends Component
{
    use WithFileUploads;

    #[Validate('required|string|min:5|max:255')]
    public string $subject = '';

    #[Validate('required|string|min:10|max:10000')]
    public string $description = '';

    #[Validate('nullable|exists:categories,id')]
    public string $categoryId = '';

    /** @var array<int, TemporaryUploadedFile> */
    #[Validate(['attachments.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,gif,txt,doc,docx,csv,zip'])]
    public array $attachments = [];

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return Category::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function submit(): mixed
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        return DB::transaction(function () use ($user): mixed {
            $targetConfig = SupportTargetConfig::query()->first();

            $ticket = Ticket::query()->create([
                'requester_id' => $user->id,
                'category_id' => $this->categoryId !== '' ? $this->categoryId : null,
                'subject' => $this->subject,
                'description' => $this->description,
                'status' => TicketStatus::New,
                'last_requester_message_at' => now(),
                'first_response_due_at' => $targetConfig instanceof SupportTargetConfig ? now()->addHours($targetConfig->first_response_hours) : null,
                'resolution_due_at' => $targetConfig instanceof SupportTargetConfig ? now()->addHours($targetConfig->resolution_hours) : null,
            ]);

            $message = TicketMessage::query()->create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => TicketMessageType::Public,
                'body' => $this->description,
            ]);

            foreach ($this->attachments as $attachment) {
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

            AiRun::query()->create([
                'ticket_id' => $ticket->id,
                'initiated_by_user_id' => $user->id,
                'run_type' => AiRunType::Triage,
                'status' => AiRunStatus::Queued,
                'input_hash' => hash('sha256', $this->subject.$this->description),
                'input_json' => [
                    'ticket_id' => $ticket->id,
                    'subject' => $this->subject,
                    'description' => $this->description,
                ],
            ]);

            return $this->redirect(route('requester.tickets.show', $ticket), navigate: true);
        });
    }

    public function render(): View
    {
        return view('livewire.requester.ticket-create-form', [
            'categories' => $this->getCategories(),
        ]);
    }
}
