<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
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

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:items-center sm:gap-1 sm:ml-8">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        Dashboard
                    </a>

                    @if(auth()->user()->isRequester())
                        <a href="{{ route('requester.tickets.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('requester.tickets.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            My Tickets
                        </a>
                    @endif

                    @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                        <a href="{{ route('agent.tickets.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('agent.tickets.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Ticket Queue
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.categories.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Categories
                        </a>
                        <a href="{{ route('admin.macros.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('admin.macros.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Macros
                        </a>
                        <a href="{{ route('admin.targets.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('admin.targets.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Response Targets 
                        </a>
                        <a href="{{ route('admin.kb-articles.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('admin.kb-articles.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Knowledge Base
                        </a>
                        <a href="{{ route('admin.ai-runs.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('admin.ai-runs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            AI Runs
                        </a>
                        <a href="{{ route('admin.audit-logs.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Audit Logs
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ auth()->user()->role->color() }}">
                    {{ auth()->user()->role->label() }}
                </span>

                <livewire:notification-bell />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition duration-150">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs uppercase shrink-0">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <span class="hidden md:block max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs text-gray-500">Signed in as</p>
                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100">
        <div class="py-2 space-y-1 px-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                Dashboard
            </a>

            @if(auth()->user()->isRequester())
                <a href="{{ route('requester.tickets.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('requester.tickets.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    My Tickets
                </a>
            @endif

            @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                <a href="{{ route('agent.tickets.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('agent.tickets.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Ticket Queue
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Categories
                </a>
                <a href="{{ route('admin.macros.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.macros.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Macros
                </a>
                <a href="{{ route('admin.targets.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.targets.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Response Targets (SLA)
                </a>
                <a href="{{ route('admin.kb-articles.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.kb-articles.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Knowledge Base
                </a>
                <a href="{{ route('admin.ai-runs.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.ai-runs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    AI Runs
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Audit Logs
                </a>
            @endif
        </div>

        <!-- Responsive Notifications -->
        <div class="px-4 py-2 border-t border-gray-100">
            <livewire:notification-bell />
        </div>

        <!-- Responsive User Info -->
        <div class="pt-3 pb-2 border-t border-gray-200 mt-1">
            <div class="flex items-center gap-3 px-4 py-2">
                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm uppercase">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="mt-1 space-y-1 px-3">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100">
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 text-left">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
