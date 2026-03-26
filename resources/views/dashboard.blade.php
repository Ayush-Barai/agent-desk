<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 tracking-tight leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Welcome Hero --}}
            <div class="relative overflow-hidden bg-white border border-gray-200 rounded-2xl shadow-sm px-8 py-10">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/50 to-white pointer-events-none"></div>
                <div class="relative flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Welcome back, {{ auth()->user()->name }}!</h1>
                        <p class="mt-2 text-gray-500 text-base max-w-xl">Overview of your support desk today. Here's what's happening and what needs your attention.</p>
                    </div>
                    <div class="hidden sm:flex shrink-0">
                        <span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-sm">
                            {{ auth()->user()->role->label() }} Account
                        </span>
                    </div>
                </div>
            </div>

            {{-- Requester Quick Actions --}}
            @if(auth()->user()->isRequester())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <a href="{{ route('requester.tickets.index') }}"
                        class="group bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-indigo-300 transition-all duration-300 flex items-center gap-5 hover:-translate-y-1">
                        <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition-colors duration-300">
                            <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">My Tickets</p>
                            <p class="text-sm text-gray-500 mt-1">View and manage your support requests</p>
                        </div>
                        <svg class="w-6 h-6 text-gray-300 group-hover:text-indigo-500 transition-colors transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('requester.tickets.create') }}"
                        class="group bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-green-300 transition-all duration-300 flex items-center gap-5 hover:-translate-y-1">
                        <div class="w-14 h-14 bg-green-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-green-600 transition-colors duration-300">
                            <svg class="w-7 h-7 text-green-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-bold text-gray-900 group-hover:text-green-700 transition-colors">New Ticket</p>
                            <p class="text-sm text-gray-500 mt-1">Submit a new support request</p>
                        </div>
                        <svg class="w-6 h-6 text-gray-300 group-hover:text-green-500 transition-colors transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            @endif

            {{-- Agent Quick Actions --}}
            @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <a href="{{ route('agent.tickets.index') }}"
                        class="group bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-indigo-300 transition-all duration-300 flex items-center gap-5 hover:-translate-y-1">
                        <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition-colors duration-300">
                            <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">Ticket Queue</p>
                            <p class="text-sm text-gray-500 mt-1">View all assigned tickets</p>
                        </div>
                    </a>

                    <a href="{{ route('agent.triage.index') }}"
                        class="group bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-amber-300 transition-all duration-300 flex items-center gap-5 hover:-translate-y-1">
                        <div class="w-14 h-14 bg-amber-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-amber-500 transition-colors duration-300">
                            <svg class="w-7 h-7 text-amber-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-bold text-gray-900 group-hover:text-amber-700 transition-colors">Triage Queue</p>
                            <p class="text-sm text-gray-500 mt-1">Review unassigned tickets</p>
                        </div>
                    </a>
                </div>
            @endif

            {{-- Admin Quick Actions --}}
            @if(auth()->user()->isAdmin())
                <div class="pt-6">
                    <h2 class="text-sm font-extrabold text-gray-400 uppercase tracking-widest mb-5">Administration</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

                        <a href="{{ route('admin.categories.index') }}"
                            class="group bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-blue-300 transition-all duration-300 flex items-start gap-4 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-blue-600 transition-colors">
                                <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-blue-700 mb-0.5">Categories</p>
                                <p class="text-xs text-gray-500 leading-relaxed">Manage ticket categories</p>
                            </div>
                        </a>

                        <a href="{{ route('admin.macros.index') }}"
                            class="group bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-emerald-300 transition-all duration-300 flex items-start gap-4 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-emerald-600 transition-colors">
                                <svg class="w-6 h-6 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-emerald-700 mb-0.5">Macros</p>
                                <p class="text-xs text-gray-500 leading-relaxed">Manage reply macros</p>
                            </div>
                        </a>

                        <a href="{{ route('admin.targets.index') }}"
                            class="group bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-amber-300 transition-all duration-300 flex items-start gap-4 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-amber-500 transition-colors">
                                <svg class="w-6 h-6 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-amber-700 mb-0.5">Response Targets</p>
                                <p class="text-xs text-gray-500 leading-relaxed">SLA configurations</p>
                            </div>
                        </a>

                        <a href="{{ route('admin.kb-articles.index') }}"
                            class="group bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-cyan-300 transition-all duration-300 flex items-start gap-4 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-cyan-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-cyan-600 transition-colors">
                                <svg class="w-6 h-6 text-cyan-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 4.462 5 2 6.343 2 8v11c0-1.657 2.462-3 5.5-3 1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c3.038 0 5.5 1.343 5.5 3v11c0-1.657-2.462-3-5.5-3-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-cyan-700 mb-0.5">Knowledge Base</p>
                                <p class="text-xs text-gray-500 leading-relaxed">Manage support articles</p>
                            </div>
                        </a>

                        <a href="{{ route('admin.ai-runs.index') }}"
                            class="group bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-violet-300 transition-all duration-300 flex items-start gap-4 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-violet-600 transition-colors">
                                <svg class="w-6 h-6 text-violet-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-violet-700 mb-0.5">AI Runs</p>
                                <p class="text-xs text-gray-500 leading-relaxed">View AI activity logs</p>
                            </div>
                        </a>

                        <a href="{{ route('admin.audit-logs.index') }}"
                            class="group bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:border-rose-300 transition-all duration-300 flex items-start gap-4 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-rose-600 transition-colors">
                                <svg class="w-6 h-6 text-rose-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-rose-700 mb-0.5">Audit Logs</p>
                                <p class="text-xs text-gray-500 leading-relaxed">Review system changes</p>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
