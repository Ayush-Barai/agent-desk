<div class="shrink-0 flex items-center">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm group-hover:bg-indigo-700 transition">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
        </div>
        <span class="text-lg font-bold text-gray-900 hidden sm:block">{{ config('app.name', 'AgentDesk') }}</span>
    </a>
</div>