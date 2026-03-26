<div class="h-[calc(100vh-14rem)] min-h-[600px] flex flex-col gap-4">
    {{-- Top Navigation / Breadcrumbs --}}
    <div class="flex items-center justify-between flex-none px-1">
        <a href="{{ route('requester.tickets.index') }}" wire:navigate class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to My Tickets
        </a>
    </div>

    <div x-data="{ activeTab: 'conversation' }" 
        class="flex flex-1 overflow-hidden bg-white shadow-sm sm:rounded-xl border border-gray-200">
        
        {{-- Left Workspace (65%) --}}
        <div class="w-[65%] flex flex-col border-r border-gray-200">
            
            {{-- Tab Bar (Sticky top) --}}
            <div class="border-b border-gray-100 bg-white/80 backdrop-blur-md z-10 flex-none px-4 pt-2">
                <nav class="flex overflow-x-auto gap-2" aria-label="Tabs" style="scrollbar-width: none;">
                    <button @click="activeTab = 'conversation'" 
                        :class="{'bg-indigo-50 text-indigo-700 font-bold': activeTab === 'conversation', 'text-gray-500 hover:text-gray-900 hover:bg-gray-50': activeTab !== 'conversation'}" 
                        class="px-4 py-2.5 rounded-t-xl text-center font-semibold text-xs sm:text-sm transition-all cursor-pointer outline-none border-b-2" :style="{ borderColor: activeTab === 'conversation' ? '#4f46e5' : 'transparent' }">
                        Conversation
                    </button>
                </nav>
            </div>

            {{-- Scrollable Viewing Area --}}
            <div class="flex-1 overflow-y-auto p-6 bg-white" id="conversation-container">
                <div x-show="activeTab === 'conversation'" class="space-y-6 flex flex-col">
                    @forelse($threadMessages as $msg)
                        @php
                            $isRequester = $msg->user_id === $ticket->requester_id;
                        @endphp
                        <div wire:key="msg-{{ $msg->id }}" class="flex {{ $isRequester ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-5 shadow-sm {{ $isRequester ? 'bg-indigo-50 border border-indigo-100 rounded-tr-sm text-indigo-900' : 'bg-white border border-gray-200 rounded-tl-sm text-gray-800' }}">
                                <div class="flex items-center justify-between mb-3 gap-4 border-b {{ $isRequester ? 'border-indigo-200/50 pb-2' : 'border-gray-100 pb-2' }}">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white {{ $isRequester ? 'bg-indigo-600' : 'bg-gray-400' }}">
                                            {{ substr($msg->author?->name ?? 'U', 0, 2) }}
                                        </div>
                                        <span class="text-sm font-bold {{ $isRequester ? 'text-indigo-900' : 'text-gray-900' }}">
                                            {{ $isRequester ? 'You' : ($msg->author?->name ?? 'Support') }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-medium {{ $isRequester ? 'text-indigo-400/80' : 'text-gray-400' }}">
                                        {{ $msg->created_at->format('M j, g:i A') }}
                                    </span>
                                </div>
                                <div class="text-[15px] max-w-none prose prose-sm {{ $isRequester ? 'text-indigo-900' : 'text-gray-800' }}">
                                    {!! nl2br(e($msg->body)) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No messages in this conversation yet.</p>
                            <p class="text-xs text-gray-400 mt-1">Start the conversation by replying below.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pinned Reply Box --}}
            <div class="border-t border-gray-200 p-4 bg-gray-50 flex-none">
                <form wire:submit="submitReply" class="flex flex-col gap-3">
                    <div class="relative">
                        <textarea wire:model="replyBody" rows="4" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm resize-none transition-shadow" 
                            placeholder="Type your response here..."></textarea>
                        @error('replyBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Attach Files
                                <input type="file" wire:model="replyAttachments" multiple class="hidden">
                            </label>
                            <div wire:loading wire:target="replyAttachments" class="text-xs text-gray-500 flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Uploading...
                            </div>
                        </div>
                        @error('replyAttachments.*') <p class="text-xs text-red-600 font-medium">{{ $message }}</p> @enderror

                        @if(count($replyAttachments) > 0)
                            <ul class="flex flex-wrap gap-2">
                                @foreach($replyAttachments as $index => $attachment)
                                    <li class="flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 bg-white shadow-sm">
                                        <span class="text-xs font-medium text-gray-600 truncate max-w-[150px]" title="{{ $attachment->getClientOriginalName() }}">
                                            {{ Str::limit($attachment->getClientOriginalName(), 20) }}
                                        </span>
                                        <button type="button" wire:click="removeReplyAttachment({{ $index }})" 
                                            class="text-gray-400 hover:text-red-500 transition-colors" 
                                            aria-label="Remove attachment">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-1 pt-1">
                        <x-action-message on="reply-sent" class="text-green-600 font-medium">Reply sent!</x-action-message>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg">
                            <span wire:loading.remove wire:target="submitReply">Send Reply</span>
                            <span wire:loading wire:target="submitReply" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Sidebar (35%) --}}
        <div class="w-[35%] overflow-y-auto bg-gray-50/50 p-6 space-y-6">
            
            {{-- Ticket Overview --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-white">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ticket Overview</h4>
                </div>
                <div class="p-5 space-y-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $ticket->subject }}</h3>
                        <p class="text-xs text-gray-400 mt-1">ID: #{{ $ticket->id }} &middot; Created {{ $ticket->created_at->format('M d, Y') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Status</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $ticket->status->color() }}">
                                {{ $ticket->status->label() }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Priority</span>
                            @if($ticket->priority)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $ticket->priority->color() }}">
                                    {{ $ticket->priority->label() }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic font-medium">Not specified</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Support Assignment --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-white">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Information</h4>
                </div>
                <div class="p-5 space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Category</span>
                        <span class="font-semibold text-gray-900">{{ $ticket->category?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-50">
                        <span class="text-gray-500">Assigned To</span>
                        <span class="font-semibold text-gray-900">{{ $ticket->assignee?->name ?? 'Waiting for Agent' }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-50">
                        <span class="text-gray-500">Last Updated</span>
                        <span class="font-semibold text-gray-900">{{ $ticket->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Existing Attachments --}}
            @if($attachments->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-white">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Attachments ({{ $attachments->count() }})</h4>
                    </div>
                    <div class="p-5">
                        <ul class="space-y-3">
                            @foreach($attachments as $attachment)
                                <li wire:key="att-{{ $attachment->id }}" class="group">
                                    <div class="flex flex-col gap-1 rounded-lg border border-gray-100 bg-white p-3 transition-all hover:border-indigo-200 hover:shadow-sm">
                                        <div class="flex items-center justify-between overflow-hidden">
                                            <a href="{{ route('attachments.download', $attachment) }}" 
                                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-2 truncate pr-2"
                                                title="Download {{ $attachment->original_name }}">
                                                <svg class="w-4 h-4 shrink-0 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                <span class="truncate">{{ $attachment->original_name }}</span>
                                            </a>
                                            <span class="shrink-0 text-[10px] font-bold text-gray-400">{{ number_format($attachment->size_bytes / 1024, 1) }} KB</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-[10px] text-gray-400">By {{ $attachment->uploader?->name === Auth::user()->name ? 'You' : $attachment->uploader?->name }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $attachment->created_at->format('M d') }}</span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Help Sidebar --}}
            <div class="bg-indigo-600 rounded-xl shadow-md p-6 text-white overflow-hidden relative">
                <div class="relative z-10">
                    <h4 class="text-sm font-bold uppercase tracking-widest opacity-80 mb-2">Need more help?</h4>
                    <p class="text-xs leading-relaxed opacity-90">Our agents are doing their best to resolve your ticket as quickly as possible. Please provide as much detail as you can in your replies.</p>
                </div>
                <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-white opacity-10" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
            </div>

        </div>
    </div>
</div>
