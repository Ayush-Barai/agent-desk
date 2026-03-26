<div class="h-[calc(100vh-4rem)] min-h-[700px] flex flex-col gap-4">
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
        class="flex flex-1 min-h-0 overflow-hidden bg-white shadow-sm sm:rounded-xl border border-gray-200">
        
        {{-- Left Workspace (65%) --}}
        <div class="w-[65%] grid grid-rows-[auto_1fr_auto] min-h-0 border-r border-gray-100 bg-white overflow-hidden">
            
            {{-- Tab Bar (Sticky top) --}}
            <div class="border-b border-gray-100 bg-white/80 backdrop-blur-md z-10 px-4 pt-2">
                <nav class="flex overflow-x-auto gap-2" aria-label="Tabs" style="scrollbar-width: none;">
                    <button @click="setTab('public')" :class="{'bg-indigo-50 text-indigo-700 font-bold': activeTab === 'public', 'text-gray-500 hover:text-gray-900 hover:bg-gray-50': activeTab !== 'public'}" class="px-4 py-2.5 rounded-t-xl text-center font-semibold text-xs sm:text-sm transition-all cursor-pointer outline-none border-b-2" :style="{ borderColor: activeTab === 'public' ? '#4f46e5' : 'transparent' }">Public Chat</button>
                    <button @click="setTab('internal')" :class="{'bg-yellow-50 text-yellow-700 font-bold': activeTab === 'internal', 'text-gray-500 hover:text-gray-900 hover:bg-gray-50': activeTab !== 'internal'}" class="px-4 py-2.5 rounded-t-xl text-center font-semibold text-xs sm:text-sm transition-all cursor-pointer outline-none border-b-2" :style="{ borderColor: activeTab === 'internal' ? '#eab308' : 'transparent' }">Internal Notes</button>
                    <button @click="setTab('ai_triage')" :class="{'bg-purple-50 text-purple-700 font-bold': activeTab === 'ai_triage', 'text-gray-500 hover:text-gray-900 hover:bg-gray-50': activeTab !== 'ai_triage'}" class="px-4 py-2.5 rounded-t-xl text-center font-semibold text-xs sm:text-sm transition-all cursor-pointer outline-none border-b-2" :style="{ borderColor: activeTab === 'ai_triage' ? '#9333ea' : 'transparent' }">AI Triage</button>
                    <button @click="setTab('ai_draft')" :class="{'bg-purple-50 text-purple-700 font-bold': activeTab === 'ai_draft', 'text-gray-500 hover:text-gray-900 hover:bg-gray-50': activeTab !== 'ai_draft'}" class="px-4 py-2.5 rounded-t-xl text-center font-semibold text-xs sm:text-sm transition-all cursor-pointer outline-none border-b-2" :style="{ borderColor: activeTab === 'ai_draft' ? '#9333ea' : 'transparent' }">AI Generated Reply</button>
                    <button @click="setTab('summary')" :class="{'bg-indigo-50 text-indigo-700 font-bold': activeTab === 'summary', 'text-gray-500 hover:text-gray-900 hover:bg-gray-50': activeTab !== 'summary'}" class="px-4 py-2.5 rounded-t-xl text-center font-semibold text-xs sm:text-sm transition-all overflow-hidden text-ellipsis whitespace-nowrap cursor-pointer outline-none border-b-2" :style="{ borderColor: activeTab === 'summary' ? '#4f46e5' : 'transparent' }" title="Summary of the conversation">Summary</button>
                </nav>
            </div>

            {{-- Scrollable Viewing Area --}}
            <div class="overflow-y-auto p-6 bg-white min-h-0">
                
                {{-- Public Thread --}}
                <div x-show="activeTab === 'public'" x-cloak class="space-y-6 flex flex-col">
                    @forelse($publicThread as $msg)
                        @php
                            $isRequester = $msg->user_id === $ticket->requester_id;
                        @endphp
                        <div wire:key="pub-{{ $msg->id }}" class="flex {{ $isRequester ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-5 shadow-sm {{ $isRequester ? 'bg-white border border-gray-200 rounded-tl-sm' : 'bg-indigo-50 border border-indigo-100 rounded-tr-sm text-indigo-900' }}">
                                <div class="flex items-center justify-between mb-3 gap-4 border-b {{ $isRequester ? 'border-gray-100 pb-2' : 'border-indigo-200/50 pb-2' }}">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white {{ $isRequester ? 'bg-gray-400' : 'bg-indigo-600' }}">
                                            {{ substr($msg->author?->name ?? 'U', 0, 2) }}
                                        </div>
                                        <span class="text-sm font-bold {{ $isRequester ? 'text-gray-900' : 'text-indigo-900' }}">
                                            {{ $msg->author?->name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-medium {{ $isRequester ? 'text-gray-400' : 'text-indigo-400/80' }}">
                                        {{ $msg->created_at->format('M j, g:i A') }}
                                    </span>
                                </div>
                                <div class="text-[15px] max-w-none prose prose-sm {{ $isRequester ? 'text-gray-800' : 'text-indigo-900' }}">
                                    {!! nl2br(e($msg->body)) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No public messages yet.</p>
                            <p class="text-xs text-gray-400 mt-1">Start the conversation by replying below.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Internal Notes --}}
                <div x-show="activeTab === 'internal'" x-cloak class="space-y-6 flex flex-col">
                    @forelse($internalNotes as $note)
                        <div wire:key="note-{{ $note->id }}" class="flex justify-end">
                            <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl bg-gradient-to-br from-yellow-50 to-amber-50 border border-amber-200/50 p-5 shadow-sm rounded-tr-sm">
                                <div class="flex items-center justify-between mb-3 gap-4 border-b border-amber-200/50 pb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-amber-700 bg-amber-200/70">
                                            {{ substr($note->author?->name ?? 'U', 0, 2) }}
                                        </div>
                                        <span class="text-sm font-bold text-amber-900">
                                            {{ $note->author?->name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-medium text-amber-600/70">
                                        {{ $note->created_at->format('M j, g:i A') }}
                                    </span>
                                </div>
                                <div class="text-[15px] text-amber-900 whitespace-pre-wrap leading-relaxed">{{ $note->body }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-center mt-4">
                            <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No internal notes.</p>
                            <p class="text-xs text-gray-400 mt-1">Leave a hidden note for your team below.</p>
                        </div>
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

                    <div><textarea wire:model.live="replyBody" wire:key="ticket-reply-textarea-{{ $ticketId }}" rows="4" class="w-full max-h-64 rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-all" placeholder="{{ $replyType === 'internal' ? 'Add an internal note...' : 'Type your reply...' }}"></textarea>@error('replyBody')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>

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
        <div class="w-[35%] overflow-y-auto bg-gray-50/30 p-6 space-y-6 border-l border-gray-100 relative">
            
            {{-- Escalation Flag --}}
            @if($ticket->escalation_required)
                <div class="bg-red-50 border border-red-200 rounded-2xl shadow-sm ring-1 ring-red-200/50">
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 000 1.06L8.94 9l-.66.72a.75.75 0 101.06 1.06L10 10.06l.72.72a.75.75 0 101.06-1.06L11.06 9l.72-.72a.75.75 0 00-1.06-1.06L10 7.94l-.72-.72a.75.75 0 00-1.06 0z" clip-rule="evenodd" />
                            </svg>
                            <h4 class="text-sm font-bold text-red-800">Escalation Required</h4>
                        </div>
                        <p class="text-xs text-red-700/80 font-medium leading-relaxed">This ticket has been flagged by AI for immediate supervisor attention.</p>
                    </div>
                </div>
            @endif

            {{-- Ticket Metadata --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100/50 overflow-hidden ring-1 ring-black/5">
                <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-b from-white to-gray-50/50">
                    <h4 class="text-[15px] font-bold text-gray-900 line-clamp-2 leading-tight" title="{{ $ticket->subject }}">{{ $ticket->subject }}</h4>
                    <div class="flex flex-wrap items-center gap-2 mt-3 block">
                        <span class="inline-flex flex-shrink-0 items-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $ticket->status->color() }} ring-1 ring-inset ring-black/5 shadow-sm">
                            {{ $ticket->status->label() }}
                        </span>
                        @if($ticket->priority)
                        <span class="inline-flex flex-shrink-0 items-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $ticket->priority->color() }} ring-1 ring-inset ring-black/5 shadow-sm">
                            {{ $ticket->priority->label() }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 text-xs text-gray-500 mb-5 pb-5 border-b border-gray-100 border-dashed">
                        <div class="w-8 h-8 rounded-full bg-gray-100 shrink-0 flex items-center justify-center font-bold text-gray-600">
                             {{ substr($ticket->requester?->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-[13px]">{{ $ticket->requester?->name ?? '—' }}</p>
                            <p class="mt-0.5">Created {{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                                <select wire:model="status"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium text-gray-700 bg-white transition-shadow">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Priority</label>
                                <select wire:model="priority"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium text-gray-700 bg-white transition-shadow">
                                    <option value="">None</option>
                                    @foreach($priorities as $p)
                                        <option value="{{ $p->value }}">{{ $p->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Assignment Integrated --}}
                        <div class="pt-2 border-t border-gray-100 border-dashed">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Assignment</label>
                            <div class="flex flex-col gap-2">
                                <select wire:model="assigneeId"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium text-gray-700 bg-white transition-shadow">
                                    <option value="">Unassigned</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex gap-2">
                                    <button wire:click="assignTicket" type="button"
                                        wire:loading.attr="disabled"
                                        class="flex-1 inline-flex justify-center items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-widest hover:bg-gray-700 transition shadow-sm">
                                        <span wire:loading.remove wire:target="assignTicket">Assign</span>
                                        <span wire:loading wire:target="assignTicket">...</span>
                                    </button>
                                    <button wire:click="assignToMe" type="button"
                                        wire:loading.attr="disabled"
                                        class="flex-1 inline-flex justify-center items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-lg font-bold text-[10px] text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-sm">
                                        <span wire:loading.remove wire:target="assignToMe">To Me</span>
                                        <span wire:loading wire:target="assignToMe">...</span>
                                    </button>
                                </div>
                            </div>
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

                        {{-- Attachments Integrated --}}
                        @if($attachments->isNotEmpty())
                            <div class="pt-4 border-t border-gray-100 border-dashed">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Attachments</label>
                                <ul class="space-y-2">
                                    @foreach($attachments as $attachment)
                                        <li wire:key="att-{{ $attachment->id }}" class="flex flex-col gap-0.5 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                                            <div class="flex items-center justify-between gap-2">
                                                <a href="{{ route('attachments.download', $attachment) }}" 
                                                    class="text-xs font-bold text-indigo-700 hover:text-indigo-900 flex items-center gap-1.5 truncate max-w-[80%] group"
                                                    target="_blank" rel="noopener noreferrer">
                                                    <svg class="w-3.5 h-3.5 shrink-0 text-indigo-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    <span class="truncate">{{ $attachment->original_name }}</span>
                                                </a>
                                                <span class="shrink-0 text-[10px] font-medium text-gray-400">{{ number_format($attachment->size_bytes / 1024, 0) }}K</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

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
