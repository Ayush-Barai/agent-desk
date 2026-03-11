<div>
    {{-- Ticket Metadata --}}
    <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $ticket->subject }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Created {{ $ticket->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->status->color() }}">
                    {{ $ticket->status->label() }}
                </span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $ticket->category?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Priority</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($ticket->priority)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->priority->color() }}">
                                {{ $ticket->priority->label() }}
                            </span>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Assigned To</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $ticket->assignee?->name ?? 'Unassigned' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $ticket->updated_at->diffForHumans() }}</dd>
                </div>
            </div>
        </div>
    </div>

    {{-- Conversation Thread --}}
    <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Conversation</h4>

            <div class="space-y-4">
                @forelse($threadMessages as $msg)
                    <div wire:key="msg-{{ $msg->id }}" class="rounded-lg border border-gray-200 p-4 {{ $msg->user_id === $ticket->requester_id ? 'bg-blue-50' : 'bg-gray-50' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $msg->author?->name ?? 'Unknown' }}
                                @if($msg->user_id !== $ticket->requester_id)
                                    <span class="ml-1 text-xs text-gray-500">(Support)</span>
                                @endif
                            </span>
                            <span class="text-xs text-gray-500">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $msg->body }}</div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No messages yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Attachments --}}
    @if($attachments->isNotEmpty())
        <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Attachments</h4>
                <ul class="space-y-2">
                    @foreach($attachments as $attachment)
                        <li wire:key="att-{{ $attachment->id }}" class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-2">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $attachment->original_name }}</span>
                                <span class="ml-2 text-xs text-gray-500">{{ number_format($attachment->size_bytes / 1024, 1) }} KB</span>
                            </div>
                            <span class="text-xs text-gray-500">{{ $attachment->uploader?->name ?? 'Unknown' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Reply Form --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Reply</h4>

            <form wire:submit="submitReply" class="space-y-4">
                <div>
                    <textarea wire:model="replyBody" rows="4"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Type your reply..."></textarea>
                    @error('replyBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <input type="file" wire:model="replyAttachments" multiple
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <div wire:loading wire:target="replyAttachments" class="mt-1 text-sm text-gray-500">Uploading...</div>
                    @error('replyAttachments.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                    <span wire:loading.remove wire:target="submitReply">Send Reply</span>
                    <span wire:loading wire:target="submitReply">Sending...</span>
                </button>
            </form>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('requester.tickets.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to My Tickets</a>
    </div>
</div>
