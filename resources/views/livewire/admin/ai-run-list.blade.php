<div>
    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by ticket subject..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <select wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="queued">Queued</option>
                <option value="running">Running</option>
                <option value="succeeded">Succeeded</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div>
            <select wire:model.live="typeFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="triage">Triage</option>
                <option value="reply_draft">Reply Draft</option>
                <option value="thread_summary">Thread Summary</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ticket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Initiated By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Completed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($runs as $run)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $run->run_type->label() }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $run->status->color() }}">
                                {{ $run->status->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $run->ticket?->subject ?? 'N/A' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $run->initiator?->name ?? 'System' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $run->provider ?? '—' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $run->model ?? '—' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $run->created_at->format('M d, Y H:i') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $run->completed_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <a href="{{ route('admin.ai-runs.show', $run) }}" class="text-indigo-600 hover:text-indigo-900" wire:navigate>View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">No AI runs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $runs->links() }}
    </div>
</div>
