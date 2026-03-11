<div>
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Triage Queue</h3>
        <div class="w-64">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tickets..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tickets as $ticket)
                    <tr wire:key="triage-{{ $ticket->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($ticket->subject, 60) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->requester?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->category?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('agent.tickets.show', $ticket) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No tickets in triage queue.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
