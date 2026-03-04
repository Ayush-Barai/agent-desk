<?php

use Livewire\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public function getTicketsProperty()
    {
        return Ticket::where('user_id', Auth::id())
            ->latest()
            ->get();
    }
};
?>

<div class="min-h-screen bg-gray-100 p-8">

    <div class="max-w-5xl mx-auto bg-white p-6 rounded-2xl shadow">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">
                My Tickets
            </h2>

            <a href="{{ route('tickets.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                Create Ticket
            </a>
        </div>

        <div class="space-y-4">
            @forelse ($this->tickets as $ticket)

                <a href="{{ route('tickets.show', $ticket->id) }}"
                   class="block p-4 border rounded-lg hover:bg-gray-50">

                    <div class="flex justify-between">
                        <span class="font-semibold">
                            {{ $ticket->title }}
                        </span>

                        <span class="text-sm text-gray-500">
                            {{ $ticket->status->value }}
                        </span>
                    </div>

                </a>

            @empty
                <p class="text-gray-500">No tickets found.</p>
            @endforelse
        </div>

    </div>

</div>