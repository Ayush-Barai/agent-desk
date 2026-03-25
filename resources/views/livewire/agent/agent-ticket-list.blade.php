<div>
    <div class="mb-4 flex flex-wrap items-center gap-4">
        @if(!auth()->user()->isAdmin())
        <div class="flex rounded-md shadow-sm">
            <button wire:click="$set('scope', 'mine')"
                class="px-4 py-2 text-sm font-medium {{ $scope === 'mine' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 transition ease-in-out duration-200' }} border rounded-l-md">
                My Tickets
            </button>
            <button wire:click="$set('scope', 'all')"
                class="px-4 py-2 text-sm font-medium {{ $scope === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 transition ease-in-out duration-200' }} border-t border-b border-r rounded-r-md">
                All Tickets
            </button>
        </div>
        @else
        <div class="flex rounded-md shadow-sm">
             <span class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white border rounded-md">
                All Tickets
            </span>
        </div>
        @endif

        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tickets as $ticket)
                    <tr wire:key="ticket-{{ $ticket->id }}" class="hover:bg-gray-50 transition ease-in-out duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($ticket->subject, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->requester?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->status->color() }}">
                                {{ $ticket->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->priority)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->priority->color() }}">
                                    {{ $ticket->priority->label() }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->assignee?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('agent.tickets.show', $ticket) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">View</a>
                                
                                @if(auth()->user()->isAdmin())
                                    <button 
                                        wire:click="deleteTicket('{{ $ticket->id }}')" 
                                        wire:confirm="Are you sure you want to delete this ticket?"
                                        class="text-red-600 hover:text-red-900 focus:outline-none transition ease-in-out duration-200"
                                        title="Delete Ticket">
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
