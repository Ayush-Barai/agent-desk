<div>
    <div class="mb-4 flex flex-col gap-4 sm:flex-row">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search actions..."
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />

        <select
            wire:model.live="actionFilter"
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
            <option value="">All Actions</option>
            @foreach ($actionTypes as $type)
                <option value="{{ $type }}">{{ str_replace('_', ' ', ucfirst($type)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ticket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            {{ $log->actor?->name ?? 'System' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $log->ticket?->subject ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if ($log->old_values_json || $log->new_values_json)
                                <div class="max-w-xs truncate">
                                    @if ($log->old_values_json)
                                        <span class="text-red-600">Old:</span> {{ json_encode($log->old_values_json) }}
                                    @endif
                                    @if ($log->new_values_json)
                                        <span class="text-green-600">New:</span> {{ json_encode($log->new_values_json) }}
                                    @endif
                                </div>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
