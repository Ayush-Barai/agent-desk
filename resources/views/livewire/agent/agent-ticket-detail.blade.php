<div class="h-[calc(100vh-14rem)] min-h-[600px] flex flex-col gap-4">
    {{-- Rate Limit Alert --}}
    @if($isRateLimited)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex-none">
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

    <div x-data="{ 
            activeTab: 'public',
            setTab(tab) {
                this.activeTab = tab;
                if (tab === 'public') $wire.set('replyType', 'public');
                else if (tab === 'internal') $wire.set('replyType', 'internal');
            }
        }" 
        x-init="
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    let type = $wire.get('replyType');
                    if(type === 'public' && activeTab === 'internal') {
                        activeTab = 'public';
                    } else if(type === 'internal' && activeTab === 'public') {
                        activeTab = 'internal';
                    }
                })
            })
        "
        class="flex flex-1 overflow-hidden bg-white shadow-sm sm:rounded-xl border border-gray-200">
        
        {{-- Left Workspace (65%) --}}
        <div class="w-[65%] flex flex-col border-r border-gray-200">
            
            {{-- Tab Bar (Sticky top) --}}
            <div class="border-b border-gray-200 bg-gray-50 flex-none">
                <nav class="flex overflow-x-auto" aria-label="Tabs" style="scrollbar-width: none;">
                    <button @click="setTab('public')" :class="{'border-indigo-500 text-indigo-600 bg-white ring-inset ring-1 ring-gray-100 ring-b-0': activeTab === 'public', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100': activeTab !== 'public'}" class="w-1/5 shrink-0 py-3 px-2 text-center border-b-2 font-medium text-xs sm:text-sm transition-colors cursor-pointer outline-none">Public Chat</button>
                    <button @click="setTab('internal')" :class="{'border-yellow-500 text-yellow-600 bg-white ring-inset ring-1 ring-gray-100 ring-b-0': activeTab === 'internal', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100': activeTab !== 'internal'}" class="w-1/5 shrink-0 py-3 px-2 text-center border-b-2 font-medium text-xs sm:text-sm transition-colors cursor-pointer outline-none">Internal Notes</button>
                    <button @click="setTab('ai_triage')" :class="{'border-purple-500 text-purple-600 bg-white ring-inset ring-1 ring-gray-100 ring-b-0': activeTab === 'ai_triage', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100': activeTab !== 'ai_triage'}" class="w-1/5 shrink-0 py-3 px-2 text-center border-b-2 font-medium text-xs sm:text-sm transition-colors cursor-pointer outline-none">AI Triage</button>
                    <button @click="setTab('ai_draft')" :class="{'border-purple-500 text-purple-600 bg-white ring-inset ring-1 ring-gray-100 ring-b-0': activeTab === 'ai_draft', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100': activeTab !== 'ai_draft'}" class="w-1/5 shrink-0 py-3 px-2 text-center border-b-2 font-medium text-xs sm:text-sm transition-colors cursor-pointer outline-none">AI Generated Reply</button>
                    <button @click="setTab('summary')" :class="{'border-indigo-500 text-indigo-600 bg-white ring-inset ring-1 ring-gray-100 ring-b-0': activeTab === 'summary', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-100': activeTab !== 'summary'}" class="w-1/5 shrink-0 py-3 px-2 text-center border-b-2 font-medium text-xs sm:text-sm transition-colors overflow-hidden text-ellipsis whitespace-nowrap cursor-pointer outline-none" title="Summary of the conversation">Summary</button>
                </nav>
            </div>

            {{-- Scrollable Viewing Area --}}
            <div class="flex-1 overflow-y-auto p-6 bg-white">
                
                {{-- Public Thread --}}
                <div x-show="activeTab === 'public'" x-cloak class="space-y-4">
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

                {{-- Internal Notes --}}
                <div x-show="activeTab === 'internal'" x-cloak class="space-y-4">
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

                {{-- AI Triage --}}
                <div x-show="activeTab === 'ai_triage'" x-cloak class="space-y-4">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-2">
                        <h4 class="text-md font-semibold text-gray-900">AI Triage Details</h4>
                        <button wire:click="runAiTriage" type="button" {{ $isRateLimited ? 'disabled' : '' }}
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-3 py-1.5 {{ $isRateLimited ? 'bg-gray-400 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="runAiTriage">Run AI Triage</span>
                            <span wire:loading wire:target="runAiTriage" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Working...
                            </span>
                        </button>
                    </div>

                    @if($latestTriageRun)
                        @if($latestTriageRun->status->value === 'queued' || $latestTriageRun->status->value === 'running')
                            <div class="text-sm text-blue-600" wire:poll.2s="getLatestTriageRun">
                                AI triage is {{ $latestTriageRun->status->label() }}...
                            </div>
                        @elseif($latestTriageRun->status->value === 'succeeded' && $latestTriageRun->output_json)
                            <div class="space-y-4">
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
                                        <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
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

                {{-- AI Draft --}}
                <div x-show="activeTab === 'ai_draft'" x-cloak class="space-y-4">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-2">
                        <h4 class="text-md font-semibold text-gray-900">AI Suggested Reply</h4>
                        <button wire:click="generateReply" type="button" {{ $isRateLimited ? 'disabled' : '' }}
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-3 py-1.5 {{ $isRateLimited ? 'bg-gray-400 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="generateReply">Generate Reply</span>
                            <span wire:loading wire:target="generateReply" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Working...
                            </span>
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
                            <div class="space-y-4">
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Draft Reply</span>
                                    <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm text-gray-700 whitespace-pre-wrap">{{ $latestReplyDraftRun->output_json['draft_reply'] ?? '—' }}</div>
                                </div>

                                <button wire:click="applyDraftReply('{{ $latestReplyDraftRun->id }}')" type="button"
                                    wire:loading.attr="disabled"
                                    class="w-full inline-flex justify-center items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                                    <span wire:loading.remove wire:target="applyDraftReply('{{ $latestReplyDraftRun->id }}')">Use This Draft</span>
                                    <span wire:loading wire:target="applyDraftReply('{{ $latestReplyDraftRun->id }}')" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Working...
                                    </span>
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

                {{-- Summary --}}
                <div x-show="activeTab === 'summary'" x-cloak class="space-y-4">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-2">
                        <h4 class="text-md font-semibold text-gray-900">AI Thread Summary</h4>
                        <button wire:click="runThreadSummary" type="button" {{ $isRateLimited ? 'disabled' : '' }}
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-3 py-1.5 {{ $isRateLimited ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="runThreadSummary">Summarize</span>
                            <span wire:loading wire:target="runThreadSummary" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Working...
                            </span>
                        </button>
                    </div>

                    @if($latestThreadSummaryRun)
                        @if(in_array($latestThreadSummaryRun->status->value, ['queued', 'running']))
                            <div class="text-sm text-blue-600" wire:poll.2s="getLatestThreadSummaryRun">
                                <span class="inline-flex items-center gap-1">
                                    {{ $latestThreadSummaryRun->status->label() }}...
                                </span>
                            </div>
                        @elseif($latestThreadSummaryRun->status->value === 'succeeded' && $latestThreadSummaryRun->output_json)
                            <div class="space-y-4">
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Summary</span>
                                    <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm text-gray-700 whitespace-pre-wrap">{{ $latestThreadSummaryRun->output_json['thread_summary'] ?? '—' }}</div>
                                </div>

                                @if(!empty($latestThreadSummaryRun->output_json['recommended_next_action']))
                                    <div>
                                        <span class="text-xs font-medium text-gray-500 uppercase">Recommended Next Action</span>
                                        <div class="mt-1 p-3 bg-green-50 rounded-md text-sm text-green-700">{{ $latestThreadSummaryRun->output_json['recommended_next_action'] }}</div>
                                    </div>
                                @endif

                                <button wire:click="insertSummaryAsNote('{{ $latestThreadSummaryRun->id }}')" type="button"
                                    wire:loading.attr="disabled"
                                    class="w-full inline-flex justify-center items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                                    <span wire:loading.remove wire:target="insertSummaryAsNote('{{ $latestThreadSummaryRun->id }}')">Add Summary as Internal Note</span>
                                    <span wire:loading wire:target="insertSummaryAsNote('{{ $latestThreadSummaryRun->id }}')" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Working...
                                    </span>
                                </button>
                            </div>
                        @elseif($latestThreadSummaryRun->status->value === 'failed')
                            <p class="text-sm text-red-600">Thread summary failed: {{ $latestThreadSummaryRun->error_message ?? 'Unknown error' }}</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 italic">Click "Summarize" to generate an AI-powered summary of the entire conversation thread.</p>
                    @endif
                </div>

            </div>

            {{-- Pinned Reply Box --}}
            <div class="border-t border-gray-200 p-4 bg-gray-50 flex-none pb-4">
                <form wire:submit="submitReply" class="flex flex-col gap-3">
                    <div class="flex gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="replyType" value="public" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Public Reply</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="replyType" value="internal" class="text-yellow-600 focus:ring-yellow-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Internal Note</span>
                        </label>
                    </div>

                    <div><textarea wire:model.live="replyBody" wire:key="ticket-reply-textarea-{{ $ticketId }}" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ $replyType === 'internal' ? 'Add an internal note...' : 'Type your reply...' }}"></textarea>@error('replyBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror</div>

                    {{-- Macro Insertion --}}
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
                                class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                                Insert
                            </button>
                        </div>
                    @endif

                    <div class="flex flex-col gap-2">
                        <input type="file" wire:model="replyAttachments" multiple
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <div wire:loading wire:target="replyAttachments" class="text-xs text-gray-500">Uploading...</div>
                        @error('replyAttachments.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                        @if(count($replyAttachments) > 0)
                            <ul class="space-y-1">
                                @foreach($replyAttachments as $index => $attachment)
                                    <li class="flex items-center justify-between rounded-md border border-gray-200 px-2 py-1 bg-white">
                                        <span class="text-xs font-medium text-gray-700 truncate mr-2" title="{{ $attachment->getClientOriginalName() }}">
                                            {{ Str::limit($attachment->getClientOriginalName(), 30) }}
                                        </span>
                                        <button type="button" wire:click="removeReplyAttachment({{ $index }})" 
                                            class="shrink-0 text-red-500 hover:text-red-700 transition" 
                                            aria-label="Remove {{ $attachment->getClientOriginalName() }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-1">
                        <x-action-message on="reply-sent">Reply sent.</x-action-message>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 {{ $replyType === 'internal' ? 'bg-yellow-600 hover:bg-yellow-700 focus-visible:ring-yellow-500' : 'bg-indigo-600 hover:bg-indigo-700 focus-visible:ring-indigo-500' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="submitReply">
                                {{ $replyType === 'internal' ? 'Add Note' : 'Send Reply' }}
                            </span>
                            <span wire:loading wire:target="submitReply" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Working...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Sidebar (35%) --}}
        <div class="w-[35%] overflow-y-auto bg-gray-50/50 p-6 space-y-6">
            
            {{-- Ticket Metadata --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h4 class="text-sm font-semibold text-gray-900 truncate" title="{{ $ticket->subject }}">{{ $ticket->subject }}</h4>
                    <div class="flex items-center gap-1 mt-1 sm:mt-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ticket->status->color() }}">
                            {{ $ticket->status->label() }}
                        </span>
                        @if($ticket->priority)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ticket->priority->color() }}">
                            {{ $ticket->priority->label() }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">Created {{ $ticket->created_at->diffForHumans() }} by <span class="font-medium text-gray-900">{{ $ticket->requester?->name ?? '—' }}</span></p>

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
                            <div class="space-y-1 max-h-32 overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                                @foreach($allTags as $tag)
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="selectedTagIds" value="{{ $tag->id }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 shadow-sm">
                                        <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2">
                            <button wire:click="updateMetadata" type="button"
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                                <span wire:loading.remove wire:target="updateMetadata">Save Metadata</span>
                                <span wire:loading wire:target="updateMetadata" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Working...
                                </span>
                            </button>
                            <div class="mt-2 text-right">
                                <x-action-message on="saved">Saved.</x-action-message>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assignment --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50">
                    <h4 class="text-sm font-semibold text-gray-900">Assignment</h4>
                </div>
                <div class="p-4 space-y-3">
                    <select wire:model="assigneeId"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <button wire:click="assignTicket" type="button"
                            wire:loading.attr="disabled"
                            class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="assignTicket">Assign</span>
                            <span wire:loading wire:target="assignTicket">...</span>
                        </button>
                        <button wire:click="assignToMe" type="button"
                            wire:loading.attr="disabled"
                            class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="assignToMe">Assign to Me</span>
                            <span wire:loading wire:target="assignToMe">...</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Escalation Flag --}}
            @if($ticket->escalation_required)
                <div class="bg-red-50 border border-red-200 rounded-lg shadow-sm">
                    <div class="p-4">
                        <h4 class="text-sm font-semibold text-red-800 mb-1">Escalation Required</h4>
                        <p class="text-xs text-red-700">This ticket has been flagged for escalation.</p>
                    </div>
                </div>
            @endif

            {{-- Attachments --}}
            @if($attachments->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50">
                        <h4 class="text-sm font-semibold text-gray-900">Attachments</h4>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-2">
                            @foreach($attachments as $attachment)
                                <li wire:key="att-{{ $attachment->id }}" class="flex flex-col gap-1 rounded-md border border-gray-100 bg-gray-50 px-3 py-2">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('attachments.download', $attachment) }}" 
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-900 hover:underline flex items-center gap-1 truncate max-w-[80%]"
                                            title="Download {{ $attachment->original_name }}">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span class="truncate">{{ $attachment->original_name }}</span>
                                        </a>
                                        <span class="shrink-0 text-xs text-gray-500">{{ number_format($attachment->size_bytes / 1024, 1) }} KB</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 pl-5">Uploaded by: {{ $attachment->uploader?->name ?? 'Unknown' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Danger Zone --}}
            @if(auth()->user()->isAdmin())
                <div class="bg-red-50 rounded-lg shadow-sm border border-red-200">
                    <div class="px-4 py-3 border-b border-red-100 bg-red-100/50">
                        <h4 class="text-sm font-semibold text-red-900">Danger Zone</h4>
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-red-700 mb-3">Deleting a ticket is permanent and cannot be undone.</p>
                        <button wire:click="deleteTicket" 
                            wire:confirm="Are you sure you want to delete this ticket? This action cannot be undone."
                            type="button"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus-visible:outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-200">
                            <span wire:loading.remove wire:target="deleteTicket">Delete Ticket</span>
                            <span wire:loading wire:target="deleteTicket" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Working...
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
