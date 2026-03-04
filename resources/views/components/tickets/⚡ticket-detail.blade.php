<?php

use Livewire\Component;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public string $ticketId;
    public string $message = '';

    public Ticket $ticket;

    public function mount(string $id)
    {
        $this->ticketId = $id;

        $this->ticket = Ticket::with(['messages.user','attachments'])
            ->findOrFail($id);
    }

    public function reply()
    {
        $validated = $this->validate([
            'message' => 'required|min:2',
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticketId,
            'user_id' => Auth::id(),
            'body' => $validated['message'],
            'is_internal' => false,
        ]);

        $this->reset('message');

        $this->ticket = $this->ticket->fresh(['messages.user','attachments']);
    }
};
?>

<div class="min-h-screen bg-gray-100 p-8">

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-2xl shadow space-y-6">

        <h2 class="text-2xl font-semibold">
            {{ $ticket->title }}
        </h2>

        <p class="text-gray-600">
            {{ $ticket->description }}
        </p>

        <hr>

        <h3 class="font-semibold">Messages</h3>

        <div class="space-y-4">
            @foreach ($ticket->messages as $msg)
                <div class="p-3 border rounded-lg">
                    <div class="text-sm text-gray-500">
                        {{ $msg->user->name }}
                    </div>
                    <div>
                        {{ $msg->body }}
                    </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="reply" class="space-y-3">

            <textarea wire:model="message"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"
                      placeholder="Write a reply...">
            </textarea>

            <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                Send Reply
            </button>

        </form>

    </div>

</div>