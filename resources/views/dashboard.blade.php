<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome Hero --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl shadow-md px-6 py-7 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p class="mt-1 text-indigo-200 text-sm">Here's what's happening with your support desk today.</p>
                </div>
                <div class="hidden sm:block">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold bg-white/20 text-white ring-1 ring-white/30">
                        {{ auth()->user()->role->label() }}
                    </span>
                </div>
            </div>

            {{-- Requester Quick Actions --}}
            @if(auth()->user()->isRequester())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('requester.tickets.index') }}"
                        class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex items-center gap-4">
                        <div class="w-11 h-11 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition">
                            <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">My Tickets</p>
                            <p class="text-xs text-gray-500 mt-0.5">View and manage your support requests</p>
                        </div>
                        <svg class="ml-auto w-5 h-5 text-gray-300 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('requester.tickets.create') }}"
                        class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex items-center gap-4">
                        <div class="w-11 h-11 bg-green-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-green-600 transition">
                            <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">New Ticket</p>
                            <p class="text-xs text-gray-500 mt-0.5">Submit a new support request</p>
                        </div>
                        <svg class="ml-auto w-5 h-5 text-gray-300 group-hover:text-green-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif

            {{-- Agent Quick Actions --}}
            @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('agent.tickets.index') }}"
                        class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex items-center gap-4">
                        <div class="w-11 h-11 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition">
                            <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Ticket Queue</p>
                            <p class="text-xs text-gray-500 mt-0.5">View all assigned tickets</p>
                        </div>
                        <svg class="ml-auto w-5 h-5 text-gray-300 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('agent.triage.index') }}"
                        class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-amber-300 transition flex items-center gap-4">
                        <div class="w-11 h-11 bg-amber-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-amber-500 transition">
                            <svg class="w-6 h-6 text-amber-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Triage Queue</p>
                            <p class="text-xs text-gray-500 mt-0.5">Review unassigned tickets</p>
                        </div>
                        <svg class="ml-auto w-5 h-5 text-gray-300 group-hover:text-amber-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif

            {{-- Admin Quick Actions --}}
            @if(auth()->user()->isAdmin())
                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Administration</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        <a href="{{ route('admin.categories.index') }}"
                            class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition flex flex-col gap-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition">
                                <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">Categories</p>
                            <p class="text-xs text-gray-500">Manage ticket categories</p>
                        </a>

                        <a href="{{ route('admin.macros.index') }}"
                            class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-green-300 transition flex flex-col gap-2">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-600 transition">
                                <svg class="w-5 h-5 text-green-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">Macros</p>
                            <p class="text-xs text-gray-500">Manage reply macros</p>
                        </a>

                        <a href="{{ route('admin.targets.index') }}"
                            class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-amber-300 transition flex flex-col gap-2">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-500 transition">
                                <svg class="w-5 h-5 text-amber-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">Response Targets (SLA)</p>
                            <p class="text-xs text-gray-500">Configure SLA response and resolution targets</p>
                        </a>

                        <a href="{{ route('admin.kb-articles.index') }}"
                            class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-cyan-300 transition flex flex-col gap-2">
                            <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:bg-cyan-600 transition">
                                <svg class="w-5 h-5 text-cyan-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 4.462 5 2 6.343 2 8v11c0-1.657 2.462-3 5.5-3 1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c3.038 0 5.5 1.343 5.5 3v11c0-1.657-2.462-3-5.5-3-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">Knowledge Base</p>
                            <p class="text-xs text-gray-500">Manage support knowledge articles</p>
                        </a>

                        <a href="{{ route('admin.ai-runs.index') }}"
                            class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex flex-col gap-2">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-600 transition">
                                <svg class="w-5 h-5 text-indigo-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">AI Runs</p>
                            <p class="text-xs text-gray-500">View AI activity logs</p>
                        </a>

                        <a href="{{ route('admin.audit-logs.index') }}"
                            class="group bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-rose-300 transition flex flex-col gap-2">
                            <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center group-hover:bg-rose-600 transition">
                                <svg class="w-5 h-5 text-rose-600 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">Audit Logs</p>
                            <p class="text-xs text-gray-500">Review administrative and system changes</p>
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
