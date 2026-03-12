<div>
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Agent Work Report</h2>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Agent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tickets Assigned</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Replies Sent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Internal Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status Changes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Resolved</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">AI Runs</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($agentMetrics as $metric)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $metric['agent']->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $metric['tickets_assigned'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $metric['replies_sent'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $metric['internal_notes'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $metric['status_changes'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $metric['resolved_tickets'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $metric['ai_runs_initiated'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No agents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
