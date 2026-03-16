<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AgentDesk') }} &mdash; AI-Powered Support</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes floatY {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }
        @keyframes pulse-ring {
            0%   { transform: scale(0.9); opacity: 0.6; }
            70%  { transform: scale(1.4); opacity: 0; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0; }
        }
        .anim-fade-up    { animation: fadeUp 0.7s ease both; }
        .anim-fade-up-d1 { animation: fadeUp 0.7s 0.12s ease both; }
        .anim-fade-up-d2 { animation: fadeUp 0.7s 0.24s ease both; }
        .anim-fade-up-d3 { animation: fadeUp 0.7s 0.36s ease both; }
        .anim-fade-up-d4 { animation: fadeUp 0.7s 0.48s ease both; }
        .anim-fade-in-d5 { animation: fadeIn 0.6s 0.6s ease both; }
        .anim-float      { animation: floatY 4s ease-in-out infinite; }
        .anim-float-slow { animation: floatY 6s ease-in-out infinite; }
        .pulse-ring::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: currentColor;
            animation: pulse-ring 2s ease-out infinite;
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-bg {
            background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(99,102,241,0.15) 0%, transparent 70%),
                        radial-gradient(ellipse 40% 40% at 80% 80%, rgba(139,92,246,0.08) 0%, transparent 60%),
                        #f9fafb;
        }
        .card-hover { transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(99,102,241,0.12); }
        .shimmer-btn {
            background: linear-gradient(90deg, #4f46e5 0%, #6366f1 40%, #818cf8 60%, #6366f1 80%, #4f46e5 100%);
            background-size: 400px 100%;
            animation: shimmer 3s infinite linear;
            transition: opacity 0.2s;
        }
        .shimmer-btn:hover { opacity: 0.9; }
        .cursor-blink::after { content: '|'; animation: blink 1s step-end infinite; color: #6366f1; font-weight: 300; margin-left: 2px; }
        .grid-bg {
            background-image: linear-gradient(rgba(99,102,241,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(99,102,241,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 hero-bg grid-bg min-h-screen">

    <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur border-b border-gray-200/60 anim-fade-in-d5">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                              d="M8 10h.01M12 10h.01M16 10h.01M21 16c0 1.1-.9 2-2 2H7l-4 4V6c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2v10z"/>
                    </svg>
                </div>
                <span class="font-bold text-gray-900 tracking-tight">{{ config('app.name', 'AgentDesk') }}</span>
            </div>
            @if (Route::has('login'))
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg transition shadow-sm">
                            Dashboard
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-sm font-medium text-gray-600 hover:text-indigo-700 transition px-3 py-1.5 rounded-lg hover:bg-indigo-50">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center gap-1 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg transition shadow-sm">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 pt-32 pb-20">

        <div class="flex justify-center anim-fade-up">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full px-3 py-1">
                <span class="relative flex h-2 w-2 pulse-ring text-indigo-400">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                AI-Powered Customer Support Platform
            </span>
        </div>

        <div class="mt-6 text-center anim-fade-up-d1">
            <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight leading-[1.08]">
                <span class="text-gray-900">Support that</span><br>
                <span class="gradient-text cursor-blink">thinks ahead</span>
            </h1>
            <p class="mt-5 text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">
                AgentDesk combines AI triage, smart reply drafts, and knowledge-base search
                to help your team resolve tickets faster &mdash; with less effort.
            </p>
        </div>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4 anim-fade-up-d2">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="shimmer-btn inline-flex items-center gap-2 text-sm font-bold text-white px-7 py-3 rounded-xl shadow-lg">
                    Go to Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @else
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="shimmer-btn inline-flex items-center gap-2 text-sm font-bold text-white px-7 py-3 rounded-xl shadow-lg">
                        Start Free
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endif
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 hover:border-indigo-300 hover:text-indigo-700 px-7 py-3 rounded-xl shadow-sm transition">
                    Sign in
                </a>
            @endauth
        </div>

        <div class="mt-6 flex justify-center anim-fade-up-d3">
            <p class="text-xs text-gray-400 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                No credit card required &bull; Free to get started
            </p>
        </div>

        <div class="mt-20 grid grid-cols-1 sm:grid-cols-3 gap-5 anim-fade-up-d3">
            <div class="card-hover bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mb-4 anim-float">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">AI Triage</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Automatically classify, prioritize, and route incoming tickets so agents focus on what matters most.
                </p>
            </div>
            <div class="card-hover bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center mb-4 anim-float-slow">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">Reply Drafts</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Generate context-aware reply suggestions from your knowledge base, reviewed and sent by agents.
                </p>
            </div>
            <div class="card-hover bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mb-4 anim-float">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">SLA Tracking</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Set response and resolution targets per priority level with real-time breach warnings.
                </p>
            </div>
        </div>

        <div class="mt-16 bg-white border border-gray-200 rounded-2xl shadow-sm anim-fade-up-d4 overflow-hidden">
            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                <div class="px-8 py-7 text-center">
                    <div class="text-3xl font-extrabold gradient-text">3&times;</div>
                    <div class="mt-1 text-xs text-gray-500 font-medium">Faster First Response</div>
                </div>
                <div class="px-8 py-7 text-center">
                    <div class="text-3xl font-extrabold gradient-text">60%</div>
                    <div class="mt-1 text-xs text-gray-500 font-medium">Tickets Auto-Triaged</div>
                </div>
                <div class="px-8 py-7 text-center">
                    <div class="text-3xl font-extrabold gradient-text">90%</div>
                    <div class="mt-1 text-xs text-gray-500 font-medium">Agent Satisfaction Score</div>
                </div>
            </div>
        </div>

        <div class="mt-20 anim-fade-up-d4">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-gray-900">How it works</h2>
                <p class="mt-2 text-sm text-gray-500">From submission to resolution in three simple steps.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="relative flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">01</div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm text-gray-900 mb-1">Customer submits ticket</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">Via the support portal, the ticket is instantly captured and queued for AI processing.</p>
                    </div>
                </div>
                <div class="relative flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xs font-bold">02</div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm text-gray-900 mb-1">AI triages &amp; drafts reply</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">The AI agent classifies priority, assigns category, and drafts a knowledge-base-grounded reply suggestion.</p>
                    </div>
                </div>
                <div class="relative flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">03</div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm text-gray-900 mb-1">Agent reviews &amp; sends</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">Your agent reviews the AI draft, makes any edits, and sends the final response with one click.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-gray-200 bg-white mt-10 anim-fade-in-d5">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded bg-indigo-600 flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                              d="M8 10h.01M12 10h.01M16 10h.01M21 16c0 1.1-.9 2-2 2H7l-4 4V6c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2v10z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-700">{{ config('app.name', 'AgentDesk') }}</span>
            </div>
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'AgentDesk') }}. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
