<div class="space-y-6">
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden ring-1 ring-black/5">
        <div class="border-b border-gray-100 px-6 py-5 bg-gradient-to-b from-white to-gray-50/50 flex justify-between items-center">
            <h2 class="text-[17px] font-bold text-gray-900">AI Run Details</h2>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $aiRun->status->color() }} ring-1 ring-inset ring-black/5 shadow-sm">
                {{ $aiRun->status->label() }}
            </span>
        </div>

        <div class="px-6 py-6">
            <dl class="grid grid-cols-1 gap-x-8 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Run Type</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $aiRun->run_type->label() }}</dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Ticket</dt>
                    <dd class="text-sm font-semibold text-gray-900">
                        @if ($aiRun->ticket)
                            <a href="{{ route('agent.tickets.show', $aiRun->ticket) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline line-clamp-1" title="{{ $aiRun->ticket->subject }}">
                                {{ $aiRun->ticket->subject }}
                            </a>
                        @else
                            <span class="text-gray-400 italic">N/A</span>
                        @endif
                    </dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Initiated By</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $aiRun->initiator?->name ?? 'System' }}</dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Provider</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $aiRun->provider ?? '—' }}</dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 lg:col-span-2">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Input Hash</dt>
                    <dd class="text-xs font-mono font-medium text-gray-600 break-all">{{ $aiRun->input_hash ?? '—' }}</dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Created At</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $aiRun->created_at->format('M d, Y H:i:s') }}</dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Started At</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $aiRun->started_at?->format('M d, Y H:i:s') ?? '—' }}</dd>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <dt class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Completed At</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $aiRun->completed_at?->format('M d, Y H:i:s') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        @if ($aiRun->error_message)
            <div class="border-t border-red-100 bg-red-50/50 px-6 py-6 ring-1 ring-inset ring-red-500/10 mb-[-1px]">
                <h3 class="text-[13px] font-bold text-red-800 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Error Message
                </h3>
                <p class="text-sm text-red-700 bg-white p-4 rounded-xl border border-red-100 font-mono text-xs whitespace-pre-wrap leading-relaxed shadow-sm">{{ $aiRun->error_message }}</p>
            </div>
        @endif

        @if ($aiRun->input_json)
            <div class="border-t border-gray-100 px-6 py-6 bg-gray-50/30">
                <h3 class="text-[13px] font-bold text-gray-600 uppercase tracking-wider mb-3">Payload Input</h3>
                <div class="bg-[#1e1e2e] rounded-xl p-4 overflow-x-auto border border-gray-800 shadow-inner">
                    <pre class="text-xs font-mono text-[#a6accd]">{{ json_encode($aiRun->input_json, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif

        @if ($aiRun->output_json)
            <div class="border-t border-gray-100 px-6 py-6 bg-gray-50/30">
                <h3 class="text-[13px] font-bold text-gray-600 uppercase tracking-wider mb-3">Payload Output</h3>
                <div class="bg-[#1e1e2e] rounded-xl p-4 overflow-x-auto border border-gray-800 shadow-inner">
                    <pre class="text-xs font-mono text-[#a6accd]">{{ json_encode($aiRun->output_json, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-4 pb-8">
        <a href="{{ route('admin.ai-runs.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors group" wire:navigate>
            <span class="w-8 h-8 rounded-full bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </span>
            Back to AI Runs
        </a>
    </div>
</div>
