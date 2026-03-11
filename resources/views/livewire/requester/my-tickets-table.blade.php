<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search tickets..."
                class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

            <select wire:model.live="status"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="priority"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">All Priorities</option>
                @foreach($priorities as $p)
                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('requester.tickets.create') }}" wire:navigate
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
            New Ticket
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        @if($tickets->isEmpty())
            <div class="p-6 text-center text-gray-500">
                No tickets found. <a href="{{ route('requester.tickets.create') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800">Create your first ticket</a>.
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tickets as $ticket)
                        <tr class="hover:bg-gray-50 cursor-pointer" wire:key="{{ $ticket->id }}">
                            <td class="px-6 py-4">
                                <a href="{{ route('requester.tickets.show', $ticket) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $ticket->subject }}
                                </a>
                                @if($ticket->category)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $ticket->category->name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->status->color() }}">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->priority)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->priority->color() }}">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $ticket->assignee?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $ticket->updated_at->diffForHumans() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 border-t">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
