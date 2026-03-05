<?php

declare(strict_types=1);


namespace App\Livewire\Dashboard;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public User $user;

    public int $openCount = 0;
    public int $inProgressCount = 0;
    public int $resolvedCount = 0;

    public function mount(): void
    {
        $this->user = Auth::user();

        $this->loadStats();
    }

    private function loadStats(): void
    {
        if ($this->user->isRequester()) {
            $query = Ticket::where('user_id', $this->user->id);
        } elseif ($this->user->isAgent()) {
            $query = Ticket::where('assigned_to', $this->user->id);
        } else {
            $query = Ticket::query();
        }

        $this->openCount = (clone $query)
            ->where('status', TicketStatus::Open)
            ->count();

        $this->inProgressCount = (clone $query)
            ->where('status', TicketStatus::InProgress)
            ->count();

        $this->resolvedCount = (clone $query)
            ->where('status', TicketStatus::Resolved)
            ->count();
    }

    /**
     * Computed property (Livewire 4 style)
     */
    public function getRecentTicketsProperty()
    {
        if ($this->user->isRequester()) {
            return Ticket::where('user_id', $this->user->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        if ($this->user->isAgent()) {
            return Ticket::where('assigned_to', $this->user->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        return Ticket::latest()
            ->limit(5)
            ->get();
    }

    public function logout(): void
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirect('/', navigate: true);
    }

};
?>
<div class="min-h-screen bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg hidden md:flex flex-col">

        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-indigo-600">
                AgentDesk
            </h2>
        </div>

        <nav class="flex-1 p-4 space-y-2 text-sm">

            <a href="{{ route('dashboard') }}"
                class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
                Dashboard
            </a>

            @if($user->isRequester())
                <a href="{{ route('tickets.create') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100">
                    Create Ticket
                </a>
            @endif

        </nav>

        <div class="p-4 border-t">
            <button wire:click="logout" class="w-full text-left px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg">
                Logout
            </button>
        </div>
    </aside>


    <!-- Main Content -->
    <div class="flex-1 flex flex-col">

        <!-- Top Navbar -->
        <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">

            <div>
                <h1 class="text-2xl font-semibold text-gray-800">
                    Dashboard
                </h1>
                <p class="text-sm text-gray-500">
                    Welcome back, {{ $user->name }}
                </p>
            </div>

            <div class="text-sm text-gray-600">
                <span class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full font-medium">
                    {{ ucfirst($user->role->value) }}
                </span>
            </div>

        </header>


        <!-- Content Area -->
        <main class="flex-1 p-8 space-y-8">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500">Open</p>
                    <h2 class="text-3xl font-bold mt-2">{{ $openCount }}</h2>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500">In Progress</p>
                    <h2 class="text-3xl font-bold mt-2">{{ $inProgressCount }}</h2>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-500">Resolved</p>
                    <h2 class="text-3xl font-bold mt-2">{{ $resolvedCount }}</h2>
                </div>

            </div>


            <!-- Recent Tickets -->
            <div class="bg-white p-6 rounded-xl shadow-sm">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">
                        Recent Tickets
                    </h2>

                    @if($user->isRequester())
                        <a href="{{ route('tickets.create') }}"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
                            + New Ticket
                        </a>
                    @endif
                </div>

                <div class="divide-y">

                    @forelse($this->recentTickets as $ticket)

                        <a href="{{ route('tickets.detail', $ticket->id) }}"
                            class="block py-4 px-4 rounded-xl hover:bg-gray-50 transition border-b">

                            <div class="flex justify-between items-center">

                                <div>
                                    <h3 class="font-semibold text-gray-800">
                                        {{ $ticket->title }}
                                    </h3>

                                    <div class="flex items-center gap-2 mt-1 text-sm">

                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($ticket->status->value === 'open') bg-green-100 text-green-700
                            @elseif($ticket->status->value === 'in_progress') bg-yellow-100 text-yellow-700
                            @elseif($ticket->status->value === 'resolved') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700
                            @endif
                        ">
                                            {{ ucfirst($ticket->status->value) }}
                                        </span>

                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($ticket->priority->value === 'high') bg-red-100 text-red-700
                            @elseif($ticket->priority->value === 'medium') bg-orange-100 text-orange-700
                            @else bg-gray-100 text-gray-700
                            @endif
                        ">
                                            {{ ucfirst($ticket->priority->value) }}
                                        </span>

                                    </div>
                                </div>

                                <span class="text-xs text-gray-400">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </span>

                            </div>

                        </a>

                    @empty
                        <p class="text-gray-500 text-sm py-4">
                            No tickets found.
                        </p>
                    @endforelse

                </div>
            </div>

        </main>

    </div>
</div>