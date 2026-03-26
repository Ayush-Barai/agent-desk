<div class="space-y-6">
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden ring-1 ring-black/5">
        <div class="border-b border-gray-100 px-6 py-5 bg-gradient-to-b from-white to-gray-50/50 flex justify-between items-center">
            <h2 class="text-[17px] font-bold text-gray-900">Agent Work Report</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Agent</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-center">Tickets Assigned</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-center">Replies Sent</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-center">Internal Notes</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-center">Status Changes</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-center">Resolved</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-center">AI Runs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($agentMetrics as $metric)
                        <tr class="hover:bg-indigo-50/40 transition-colors duration-200">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs ring-2 ring-white shadow-sm">
                                        {{ substr($metric['agent']->name, 0, 1) }}
                                    </div>
                                    <div class="font-semibold text-gray-900 text-sm">{{ $metric['agent']->name }}</div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 font-medium text-center">{{ $metric['tickets_assigned'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 font-medium text-center">{{ $metric['replies_sent'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 font-medium text-center">{{ $metric['internal_notes'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 font-medium text-center">{{ $metric['status_changes'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 font-medium text-center">
                                <span class="{{ $metric['resolved_tickets'] > 0 ? 'text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md' : '' }}">
                                    {{ $metric['resolved_tickets'] }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 font-medium text-center">{{ $metric['ai_runs_initiated'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900">No agent metrics found</p>
                                    <p class="text-sm mt-1 text-gray-500">There is currently no data to display for any agents.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
