<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Rate Limit Alert --}}
    @if($isRateLimited)
        <div class="lg:col-span-3 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-red-800">{{ $rateLimitError }}</h3>
                    @if($remainingAiAttempts > 0)
                        <p class="text-xs text-red-700 mt-1">You have {{ $remainingAiAttempts }} AI run(s) remaining this hour.</p>
                    @endif
                </div>
                <button 
                    wire:click="clearRateLimitError"
                    class="text-red-600 hover:text-red-800 focus:outline-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Left Column: Thread & Reply --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Public Thread --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Public Conversation</h4>
                <div class="space-y-4">
                    @forelse($publicThread as $msg)
                        <div wire:key="pub-{{ $msg->id }}" class="rounded-lg border border-gray-200 p-4 {{ $msg->user_id === $ticket->requester_id ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $msg->author?->name ?? 'Unknown' }}
                                    @if($msg->user_id === $ticket->requester_id)
                                        <span class="ml-1 text-xs text-blue-600">(Requester)</span>
                                    @else
                                        <span class="ml-1 text-xs text-gray-500">(Agent)</span>
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500">{{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $msg->body }}</div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No public messages yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Internal Notes --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-400">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Internal Notes</h4>
                <div class="space-y-4">
                    @forelse($internalNotes as $note)
                        <div wire:key="note-{{ $note->id }}" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">{{ $note->author?->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $note->body }}</div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No internal notes.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Reply Form --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Reply</h4>

                <form wire:submit="submitReply" class="space-y-4">
                    <div class="flex gap-4 mb-2">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="replyType" value="public" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Public Reply</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="replyType" value="internal" class="text-yellow-600 focus:ring-yellow-500">
                            <span class="ml-2 text-sm text-gray-700">Internal Note</span>
                        </label>
                    </div>

                    <div>
                        <textarea 
                            wire:model.live="replyBody" 
                            wire:key="ticket-reply-textarea-{{ $ticketId }}"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="{{ $replyType === 'internal' ? 'Add an internal note...' : 'Type your reply...' }}">
                        </textarea>
                        @error('replyBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Macro Insertion - Only for agents/admins composing public replies --}}
                    @if($replyType === 'public' && count($macros) > 0)
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-700 uppercase mb-1">Insert Macro</label>
                                <select 
                                    wire:model="selectedMacroId"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">— Select a macro —</option>
                                    @foreach($macros as $macro)
                                        <option value="{{ $macro->id }}">{{ $macro->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button 
                                type="button"
                                wire:click="insertMacro"
                                class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                                Insert
                            </button>
                        </div>
                    @endif

                    <div>
                        <input type="file" wire:model="replyAttachments" multiple
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <div wire:loading wire:target="replyAttachments" class="mt-1 text-sm text-gray-500">Uploading...</div>
                        @error('replyAttachments.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                        @if(count($replyAttachments) > 0)
                            <ul class="mt-3 space-y-2">
                                @foreach($replyAttachments as $index => $attachment)
                                    <li class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 bg-gray-50">
                                        <span class="text-sm font-medium text-gray-700 truncate mr-4" title="{{ $attachment->getClientOriginalName() }}">
                                            {{ Str::limit($attachment->getClientOriginalName(), 40) }}
                                        </span>
                                        <button type="button" wire:click="removeReplyAttachment({{ $index }})" 
                                            class="shrink-0 text-red-500 hover:text-red-700 transition" 
                                            aria-label="Remove {{ $attachment->getClientOriginalName() }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 {{ $replyType === 'internal' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-indigo-600 hover:bg-indigo-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                        <span wire:loading.remove wire:target="submitReply">
                            {{ $replyType === 'internal' ? 'Add Note' : 'Send Reply' }}
                        </span>
                        <span wire:loading wire:target="submitReply">Sending...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right Column: Metadata & AI Panel --}}
    <div class="space-y-6">

        {{-- Ticket Metadata --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">{{ $ticket->subject }}</h4>
                <p class="text-xs text-gray-500 mb-4">Created {{ $ticket->created_at->diffForHumans() }} by {{ $ticket->requester?->name ?? '—' }}</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Status</label>
                        <select wire:model="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($statuses as $s)
                                <option value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Priority</label>
                        <select wire:model="priority"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">None</option>
                            @foreach($priorities as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Category</label>
                        <select wire:model="categoryId"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">None</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Tags</label>
                        <div class="space-y-1 max-h-32 overflow-y-auto">
                            @foreach($allTags as $tag)
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="selectedTagIds" value="{{ $tag->id }}"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button wire:click="updateMetadata" type="button"
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                        Save Metadata
                    </button>
                </div>
            </div>
        </div>

        {{-- Assignment --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Assignment</h4>

                <div class="space-y-3">
                    <select wire:model="assigneeId"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <button wire:click="assignTicket" type="button"
                            class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            Assign
                        </button>
                        <button wire:click="assignToMe" type="button"
                            class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Assign to Me
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Escalation Flag --}}
        @if($ticket->escalation_required)
            <div class="bg-red-50 border border-red-200 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-md font-semibold text-red-800 mb-2">Escalation Required</h4>
                    <p class="text-sm text-red-700">This ticket has been flagged for escalation.</p>
                </div>
            </div>
        @endif

        {{-- AI Triage Panel --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-purple-400">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-semibold text-gray-900">AI Triage</h4>
                    <button wire:click="runAiTriage" type="button" {{ $isRateLimited ? 'disabled' : '' }}
                        class="inline-flex items-center px-3 py-1.5 {{ $isRateLimited ? 'bg-gray-400 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                        <span wire:loading.remove wire:target="runAiTriage">Run AI Triage</span>
                        <span wire:loading wire:target="runAiTriage">Running...</span>
                    </button>
                </div>

                @if($latestTriageRun)
                    @if($latestTriageRun->status->value === 'queued' || $latestTriageRun->status->value === 'running')
                        <div class="text-sm text-blue-600" wire:poll.2s="getLatestTriageRun">
                            AI triage is {{ $latestTriageRun->status->label() }}...
                        </div>
                    @elseif($latestTriageRun->status->value === 'succeeded' && $latestTriageRun->output_json)
                        <div class="space-y-3">
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase">Summary</span>
                                <p class="text-sm text-gray-700 mt-1">{{ $latestTriageRun->output_json['summary'] ?? '—' }}</p>
                            </div>

                            @if(!empty($latestTriageRun->output_json['category_suggestion']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Suggested Category</span>
                                    <p class="text-sm text-gray-700 mt-1">{{ $latestTriageRun->output_json['category_suggestion'] }}</p>
                                </div>
                            @endif

                            @if(!empty($latestTriageRun->output_json['priority_suggestion']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Suggested Priority</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ ucfirst($latestTriageRun->output_json['priority_suggestion']) }}
                                    </span>
                                </div>
                            @endif

                            @if(!empty($latestTriageRun->output_json['tags']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Suggested Tags</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($latestTriageRun->output_json['tags'] as $tag)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($latestTriageRun->output_json['clarifying_questions']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Clarifying Questions</span>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        @foreach($latestTriageRun->output_json['clarifying_questions'] as $question)
                                            <li class="text-sm text-gray-700">{{ $question }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($latestTriageRun->output_json['escalation_required']))
                                <div class="rounded-md bg-red-50 p-3">
                                    <p class="text-sm text-red-800 font-medium">Escalation Recommended</p>
                                </div>
                            @endif
                        </div>
                    @elseif($latestTriageRun->status->value === 'failed')
                        <p class="text-sm text-red-600">AI triage failed: {{ $latestTriageRun->error_message ?? 'Unknown error' }}</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500 italic">AI triage has not run yet. Click "Run AI Triage" to analyze this ticket.</p>
                @endif
            </div>
        </div>

        {{-- AI Reply Draft Panel --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-purple-400">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-semibold text-gray-900">AI Suggested Reply</h4>
                    <button wire:click="generateReply" type="button" {{ $isRateLimited ? 'disabled' : '' }}
                        class="inline-flex items-center px-3 py-1.5 {{ $isRateLimited ? 'bg-gray-400 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                        <span wire:loading.remove wire:target="generateReply">Generate Reply</span>
                        <span wire:loading wire:target="generateReply">Generating...</span>
                    </button>
                </div>

                @if($latestReplyDraftRun)
                    @if(in_array($latestReplyDraftRun->status->value, ['queued', 'running']))
                        <div class="text-sm text-blue-600" wire:poll.2s="getLatestReplyDraftRun">
                            <span class="inline-flex items-center gap-1">
                                @if($latestReplyDraftRun->progress_state)
                                    {{ $latestReplyDraftRun->progress_state }}...
                                @else
                                    {{ $latestReplyDraftRun->status->label() }}...
                                @endif
                            </span>
                        </div>
                    @elseif($latestReplyDraftRun->status->value === 'succeeded' && $latestReplyDraftRun->output_json)
                        <div class="space-y-3">
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase">Draft Reply</span>
                                <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm text-gray-700 whitespace-pre-wrap">{{ $latestReplyDraftRun->output_json['draft_reply'] ?? '—' }}</div>
                            </div>

                            <button wire:click="applyDraftReply('{{ $latestReplyDraftRun->id }}')" type="button"
                                class="w-full inline-flex justify-center items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                Use This Draft
                            </button>

                            @if(!empty($latestReplyDraftRun->output_json['next_steps']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Next Steps</span>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        @foreach($latestReplyDraftRun->output_json['next_steps'] as $step)
                                            <li class="text-sm text-gray-700">{{ $step }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($latestReplyDraftRun->output_json['risk_flags']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Risk Flags</span>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        @foreach($latestReplyDraftRun->output_json['risk_flags'] as $flag)
                                            <li class="text-sm text-red-700">{{ $flag }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($latestReplyDraftRun->output_json['retrieved_kb_snippets']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Retrieved KB Articles</span>
                                    <div class="mt-1 space-y-2">
                                        @foreach($latestReplyDraftRun->output_json['retrieved_kb_snippets'] as $snippet)
                                            <div class="p-2 bg-purple-50 rounded text-sm">
                                                <span class="font-medium text-purple-800">{{ $snippet['title'] ?? 'Untitled' }}</span>
                                                <p class="text-gray-600 text-xs mt-0.5">{{ $snippet['excerpt'] ?? '' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($latestReplyDraftRun->output_json['used_kb_snippets']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Cited KB Sources</span>
                                    <div class="mt-1 space-y-1">
                                        @foreach($latestReplyDraftRun->output_json['used_kb_snippets'] as $snippet)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mr-1">{{ $snippet['title'] ?? 'Untitled' }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif($latestReplyDraftRun->status->value === 'failed')
                        <p class="text-sm text-red-600">Reply draft failed: {{ $latestReplyDraftRun->error_message ?? 'Unknown error' }}</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500 italic">Click "Generate Reply" to draft an AI-powered response. Any text in the reply box will be used as a seed instruction.</p>
                @endif
            </div>
        </div>

        {{-- AI Clarifying Questions Placeholder --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-purple-400">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-2">Clarifying Questions</h4>
                <p class="text-sm text-gray-500 italic">AI-generated clarifying questions will appear here.</p>
            </div>
        </div>

        {{-- Attachments --}}
        @if($attachments->isNotEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Attachments</h4>
                    <ul class="space-y-2">
                        @foreach($attachments as $attachment)
                            <li wire:key="att-{{ $attachment->id }}" class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-2">
                                <div>
                                    <a href="{{ route('attachments.download', $attachment) }}" 
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900 hover:underline flex items-center gap-1"
                                        title="Download {{ $attachment->original_name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        {{ $attachment->original_name }}
                                    </a>
                                    <span class="ml-5 text-xs text-gray-500">{{ number_format($attachment->size_bytes / 1024, 1) }} KB</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $attachment->uploader?->name ?? 'Unknown' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
