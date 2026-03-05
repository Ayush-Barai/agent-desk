<?php

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {

    use WithFileUploads;

    public Ticket $ticket;

    public string $reply = '';
    public string $internalNote = '';

    public $attachment;

    public $assignee;
    public $status;
    public $priority;

    public function mount($ticketId)
    {
        $this->ticket = Ticket::with(['messages.user','attachments'])->findOrFail($ticketId);

        $this->assignee = $this->ticket->assigned_to;
        $this->status = $this->ticket->status;
        $this->priority = $this->ticket->priority;
    }

    public function postReply()
    {
        $this->validate([
            'reply' => 'required|min:3'
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'message' => $this->reply,
            'type' => 'public'
        ]);

        $this->reply = '';

        $this->ticket->refresh();
    }

    public function addInternalNote()
    {
        $this->validate([
            'internalNote' => 'required|min:3'
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'message' => $this->internalNote,
            'type' => 'internal'
        ]);

        $this->internalNote = '';

        $this->ticket->refresh();
    }

    public function uploadAttachment()
    {
        $this->validate([
            'attachment' => 'required|file|max:2048'
        ]);

        $path = $this->attachment->store('attachments');

        $this->ticket->attachments()->create([
            'path' => $path,
            'uploaded_by' => Auth::id()
        ]);

        $this->attachment = null;

        $this->ticket->refresh();
    }

    public function assignTicket()
    {
        $this->ticket->update([
            'assigned_to' => $this->assignee
        ]);

        $this->ticket->refresh();
    }

    public function updateTicket()
    {
        $this->ticket->update([
            'status' => $this->status,
            'priority' => $this->priority
        ]);

        $this->ticket->refresh();
    }

};
?>

<div class="max-w-4xl mx-auto p-6 space-y-6">

    <h1 class="text-2xl font-bold">
        {{ $ticket->title }}
    </h1>

    <div class="text-gray-600">
        Status: {{ $ticket->status }} |
        Priority: {{ $ticket->priority }}
    </div>

    <!-- Ticket Controls -->

    <div class="flex gap-4">

        <select wire:model="status" class="border p-2 rounded">
            <option value="new">New</option>
            <option value="triaged">Triaged</option>
            <option value="in_progress">In Progress</option>
            <option value="waiting">Waiting</option>
            <option value="resolved">Resolved</option>
        </select>

        <select wire:model="priority" class="border p-2 rounded">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
        </select>

        <button
            wire:click="updateTicket"
            class="bg-blue-600 text-white px-4 py-2 rounded"
        >
            Update
        </button>

    </div>

    <!-- Assign Agent -->

    <div class="flex gap-3">

        <select wire:model="assignee" class="border p-2 rounded">

            <option value="">Unassigned</option>

            @foreach(User::where('role','agent')->get() as $agent)

            <option value="{{ $agent->id }}">
                {{ $agent->name }}
            </option>

            @endforeach

        </select>

        <button
            wire:click="assignTicket"
            class="bg-purple-600 text-white px-4 py-2 rounded"
        >
            Assign
        </button>

    </div>

    <!-- Attachments -->

    <div class="space-y-2">

        <h2 class="font-bold">Attachments</h2>

        <input type="file" wire:model="attachment">

        <button
            wire:click="uploadAttachment"
            class="bg-green-600 text-white px-3 py-2 rounded"
        >
            Upload
        </button>

        <div class="space-y-1">

            @foreach($ticket->attachments as $file)

            <div>
                <a href="{{ Storage::url($file->path) }}"
                   class="text-blue-600 underline">
                    Download Attachment
                </a>
            </div>

            @endforeach

        </div>

    </div>

    <!-- Ticket Thread -->

    <div class="space-y-4">

        <h2 class="font-bold">Conversation</h2>

        @foreach($ticket->messages as $message)

            @if($message->type === 'public')

            <div class="border rounded p-3">

                <div class="text-sm text-gray-500">
                    {{ $message->user->name }}
                </div>

                <div class="mt-2">
                    {{ $message->message }}
                </div>

            </div>

            @endif

        @endforeach

    </div>

    <!-- Reply -->

    <div>

        <textarea
            wire:model="reply"
            class="w-full border rounded p-2"
            placeholder="Write reply..."
        ></textarea>

        <button
            wire:click="postReply"
            class="mt-2 bg-blue-600 text-white px-4 py-2 rounded"
        >
            Send Reply
        </button>

    </div>

    <!-- Internal Notes -->

    <div class="border-t pt-6 space-y-2">

        <h2 class="font-bold">Internal Notes</h2>

        <textarea
            wire:model="internalNote"
            class="w-full border rounded p-2"
        ></textarea>

        <button
            wire:click="addInternalNote"
            class="bg-gray-800 text-white px-4 py-2 rounded"
        >
            Add Note
        </button>

        @foreach($ticket->messages as $message)

            @if($message->type === 'internal')

            <div class="bg-yellow-50 border p-3 rounded">

                <div class="text-sm text-gray-500">
                    {{ $message->user->name }}
                </div>

                <div>
                    {{ $message->message }}
                </div>

            </div>

            @endif

        @endforeach

    </div>

</div>