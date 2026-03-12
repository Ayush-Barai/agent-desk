<div>
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">AI Run Details</h2>
        </div>

        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Run Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->run_type->label() }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $aiRun->status->color() }}">
                            {{ $aiRun->status->label() }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Ticket</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if ($aiRun->ticket)
                            {{ $aiRun->ticket->subject }}
                        @else
                            N/A
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Initiated By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->initiator?->name ?? 'System' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Provider</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->provider ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Model</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->model ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Input Hash</dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900">{{ $aiRun->input_hash ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->created_at->format('M d, Y H:i:s') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Started At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->started_at?->format('M d, Y H:i:s') ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $aiRun->completed_at?->format('M d, Y H:i:s') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        @if ($aiRun->error_message)
            <div class="border-t border-gray-200 px-6 py-4">
                <h3 class="text-sm font-medium text-red-600">Error Message</h3>
                <p class="mt-1 text-sm text-red-800">{{ $aiRun->error_message }}</p>
            </div>
        @endif

        @if ($aiRun->input_json)
            <div class="border-t border-gray-200 px-6 py-4">
                <h3 class="text-sm font-medium text-gray-500">Input</h3>
                <pre class="mt-1 overflow-x-auto rounded bg-gray-50 p-3 text-sm text-gray-800">{{ json_encode($aiRun->input_json, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if ($aiRun->output_json)
            <div class="border-t border-gray-200 px-6 py-4">
                <h3 class="text-sm font-medium text-gray-500">Output</h3>
                <pre class="mt-1 overflow-x-auto rounded bg-gray-50 p-3 text-sm text-gray-800">{{ json_encode($aiRun->output_json, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.ai-runs.index') }}" class="text-indigo-600 hover:text-indigo-900" wire:navigate>&larr; Back to AI Runs</a>
    </div>
</div>
