<div class="relative" x-data="{ open: false }" @click.outside="open = false" wire:poll.30s>
    {{-- Bell button --}}
    <button
        @click="open = !open"
        id="notification-bell-btn"
        class="relative flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition duration-150"
        aria-label="Notifications"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 rounded-xl shadow-lg bg-white ring-1 ring-black/5 z-50 origin-top-right"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition"
                    id="mark-all-read-btn"
                >
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Notification list --}}
        <ul class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    /** @var array<string, mixed> $data */
                    $data = $notification->data;
                    $ticketId = $data['ticket_id'] ?? null;
                    $message  = $data['message'] ?? 'New notification';
                @endphp
                <li wire:key="notif-{{ $notification->id }}" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition">
                    {{-- Blue dot indicator --}}
                    <span class="mt-1.5 shrink-0 w-2 h-2 rounded-full bg-indigo-500"></span>

                    <div class="flex-1 min-w-0">
                        @if($ticketId)
                            @php
                                $user = auth()->user();
                                if ($user?->isAgent() || $user?->isAdmin()) {
                                    $ticketRoute = route('agent.tickets.show', $ticketId);
                                } else {
                                    $ticketRoute = route('requester.tickets.show', $ticketId);
                                }
                            @endphp
                            <a
                                href="{{ $ticketRoute }}"
                                wire:click="markAsRead('{{ $notification->id }}')"
                                @click="open = false"
                                class="text-sm text-gray-800 hover:text-indigo-600 font-medium leading-snug block"
                            >
                                {{ $message }}
                            </a>
                        @else
                            <p class="text-sm text-gray-800 font-medium leading-snug">{{ $message }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Dismiss --}}
                    <button
                        wire:click="markAsRead('{{ $notification->id }}')"
                        class="shrink-0 text-gray-300 hover:text-gray-500 transition mt-0.5"
                        title="Dismiss"
                        aria-label="Dismiss notification"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </li>
            @empty
                <li class="flex flex-col items-center justify-center gap-1 py-8 text-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm font-medium text-gray-400">You're all caught up 🎉</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
