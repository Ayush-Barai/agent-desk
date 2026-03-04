<?php

declare(strict_types=1);

namespace App\Livewire;
use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="min-h-screen bg-gray-50 flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-indigo-600">
                AgentDesk
            </h1>

            <div class="space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-gray-700 hover:text-indigo-600 font-medium">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-gray-700 hover:text-indigo-600 font-medium">
                        Login
                    </a>

                    <a href="{{ route('register') }}"
                       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="flex-1 flex items-center justify-center text-center px-6">
        <div class="max-w-3xl">
            <h2 class="text-4xl font-bold text-gray-800 mb-6">
                Modern Helpdesk with AI-Powered Triage
            </h2>

            <p class="text-lg text-gray-600 mb-8">
                AgentDesk helps teams manage tickets, automate triage,
                and draft intelligent responses using AI — with full
                human approval and strict enterprise controls.
            </p>

            <div class="space-x-4">
                @guest
                    <a href="{{ route('register') }}"
                       class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-lg hover:bg-indigo-700">
                        Get Started
                    </a>

                    <a href="{{ route('login') }}"
                       class="border border-indigo-600 text-indigo-600 px-6 py-3 rounded-lg text-lg hover:bg-indigo-50">
                        Login
                    </a>
                @else
                    <a href="{{ route('dashboard') }}"
                       class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-lg hover:bg-indigo-700">
                        Go to Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t py-4 text-center text-sm text-gray-500">
        © {{ now()->year }} AgentDesk — AI Triage + Reply Assist
    </footer>
</div>