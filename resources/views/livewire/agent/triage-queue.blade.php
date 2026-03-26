<div class="space-y-6">
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900 tracking-tight">Triage Queue</h3>
                <p class="text-xs text-gray-500">Unassigned tickets requiring initial classification</p>
            </div>
        </div>

        <div class="relative w-full sm:w-72">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tickets..."
                class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-shadow">
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
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Category</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr wire:key="triage-{{ $ticket->id }}" class="hover:bg-amber-50/40 transition-colors duration-200 group">
                            <td class="px-6 py-4">
                                <a href="{{ route('agent.tickets.show', $ticket) }}" wire:navigate class="block">
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-amber-700 transition-colors line-clamp-1">
                                        {{ $ticket->subject }}
                                    </p>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                {{ $ticket->requester?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->category)
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                        {{ $ticket->category->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $ticket->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="{{ route('agent.tickets.show', $ticket) }}" wire:navigate class="inline-flex items-center justify-center p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 hover:text-amber-900 rounded-lg transition-colors">
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
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900">Queue is empty</p>
                                    <p class="text-sm mt-1">All tickets have been triaged and assigned.</p>
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
