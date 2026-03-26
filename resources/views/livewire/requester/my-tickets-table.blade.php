<div class="space-y-6">
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto flex-1">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search my tickets..."
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

        <div class="w-full sm:w-auto shrink-0">
            <a href="{{ route('requester.tickets.create') }}" wire:navigate
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Ticket
            </a>
        </div>
    </div>

    {{-- Ticket Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        @if($tickets->isEmpty())
            <div class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center text-gray-500">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-gray-900">No tickets found</p>
                    <p class="text-sm mt-1 max-w-sm">We couldn't find any tickets matching your criteria.</p>
                    @if(empty($search) && empty($status) && empty($priority))
                        <a href="{{ route('requester.tickets.create') }}" wire:navigate class="mt-4 font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                            Create your first ticket &rarr;
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-200">
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Subject</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Priority</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Assigned To</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-indigo-50/50 transition-colors duration-200 cursor-pointer group" wire:key="ticket-{{ $ticket->id }}" onclick="window.location.href='{{ route('requester.tickets.show', $ticket) }}'">
                                <td class="px-6 py-4 min-w-[200px]">
                                    <div class="flex flex-col gap-1">
                                        <a href="{{ route('requester.tickets.show', $ticket) }}" wire:navigate class="text-sm font-bold text-gray-900 group-hover:text-indigo-700 transition-colors line-clamp-1">
                                            {{ $ticket->subject }}
                                        </a>
                                        @if($ticket->category)
                                            <span class="inline-flex w-max items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                {{ $ticket->category->name }}
                                            </span>
                                        @endif
                                    </div>
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
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $ticket->assignee?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $tickets->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
