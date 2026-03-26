<div class="space-y-6">
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-2">
            @if(!auth()->user()->isAdmin())
                <div class="inline-flex bg-gray-100/80 p-1 rounded-lg">
                    <button wire:click="$set('scope', 'mine')"
                        class="px-4 py-1.5 text-sm font-semibold rounded-md transition-all duration-200 {{ $scope === 'mine' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-600 hover:text-gray-900' }}">
                        My Tickets
                    </button>
                    <button wire:click="$set('scope', 'all')"
                        class="px-4 py-1.5 text-sm font-semibold rounded-md transition-all duration-200 {{ $scope === 'all' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-600 hover:text-gray-900' }}">
                        All Tickets
                    </button>
                </div>
            @else
                <span class="inline-flex px-4 py-1.5 text-sm font-semibold bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100">
                    All Tickets
                </span>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                    class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
            </div>

            <select wire:model.live="status"
                class="block w-full sm:w-auto py-2 pl-3 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow bg-white text-gray-700">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="priority"
                class="block w-full sm:w-auto py-2 pl-3 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow bg-white text-gray-700">
                <option value="">All Priorities</option>
                @foreach($priorities as $p)
                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Ticket Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Subject</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Requester</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Priority</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Assigned</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Updated</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr wire:key="ticket-{{ $ticket->id }}" class="hover:bg-indigo-50/50 transition-colors duration-200 group">
                            <td class="px-6 py-4">
                                <a href="{{ route('agent.tickets.show', $ticket) }}" wire:navigate class="block">
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-indigo-700 transition-colors line-clamp-1">
                                        {{ $ticket->subject }}
                                    </p>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                {{ $ticket->requester?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $ticket->status->color() }} ring-1 ring-inset ring-black/5">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->priority)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $ticket->priority->color() }} ring-1 ring-inset ring-black/5">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($ticket->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-[10px] font-bold">
                                            {{ substr($ticket->assignee->name, 0, 2) }}
                                        </div>
                                        <span>{{ $ticket->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center text-gray-400 bg-gray-50 border border-gray-200 rounded-md px-2 py-0.5 text-xs font-medium">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ticket->updated_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="{{ route('agent.tickets.show', $ticket) }}" wire:navigate class="inline-flex items-center justify-center p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-900 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    
                                    @if(auth()->user()->isAdmin())
                                        <button 
                                            wire:click="deleteTicket('{{ $ticket->id }}')" 
                                            wire:confirm="Are you sure you want to delete this ticket?"
                                            class="inline-flex items-center justify-center p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-900 rounded-lg transition-colors"
                                            title="Delete Ticket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-base font-semibold text-gray-900">No tickets found</p>
                                    <p class="text-sm mt-1">Try adjusting your search or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
