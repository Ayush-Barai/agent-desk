<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-2">{{ __("You're logged in!") }}</p>
                    <p class="text-sm text-gray-600">
                        {{ __('Role:') }}
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ auth()->user()->role->color() }}">
                            {{ auth()->user()->role->label() }}
                        </span>
                    </p>
                </div>
            </div>

            @if(auth()->user()->isRequester())
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-2">{{ __('Quick Actions') }}</h3>
                        <a href="{{ route('requester.tickets.index') }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ __('View My Tickets') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif

            @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-2">{{ __('Agent Actions') }}</h3>
                        <a href="{{ route('agent.tickets.index') }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ __('View Ticket Queue') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif

            @if(auth()->user()->isAdmin())
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-2">{{ __('Admin Actions') }}</h3>
                        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ __('Manage Users') }} &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
