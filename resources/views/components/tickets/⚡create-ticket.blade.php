<?php

declare(strict_types=1);

namespace App\Livewire\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $description = '';
    public string $priority = 'medium';

    public array $attachments = [];

    public function create(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'priority' => ['required', 'string'],
            'attachments.*' => [
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,doc,docx,txt',
            ],
        ]);

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => TicketPriority::from($validated['priority']),
            'status' => TicketStatus::Open,
            'user_id' => Auth::id(),
        ]);

        foreach ($this->attachments as $file) {

            $path = $file->store(
                'tickets/' . $ticket->id,
                'private'
            );

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->reset();

        session()->flash('success', 'Ticket created successfully.');
        redirect('')->route('dashboard');
    }
}
?>

<div class="max-w-2xl mx-auto p-6 bg-white rounded-2xl shadow">

    <h2 class="text-xl font-semibold mb-6">
        Create New Ticket
    </h2>

    @if(session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="create" class="space-y-4">

        <!-- Title -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Title
            </label>
            <input type="text"
                   wire:model="title"
                   class="w-full border rounded-lg px-3 py-2">

            @error('title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Description
            </label>
            <textarea wire:model="description"
                      rows="5"
                      class="w-full border rounded-lg px-3 py-2">
            </textarea>

            @error('description')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Priority -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Priority
            </label>
            <select wire:model="priority"
                    class="w-full border rounded-lg px-3 py-2">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </div>

        <!-- Attachments -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Attachments
            </label>

            <input type="file"
                multiple
                wire:model="attachments"
                class="w-full border rounded-lg px-3 py-2 hover:cursor-pointer hover:bg-gray-50">

            @error('attachments.*')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror

            <div wire:loading wire:target="attachments"
                class="text-sm text-gray-500 mt-2">
                Uploading...
            </div>
        </div>

        <!-- Submit -->
        <button type="submit"
                wire:click="create"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition hover:cursor-pointer">
            Create Ticket
        </button>

    </form>

</div>