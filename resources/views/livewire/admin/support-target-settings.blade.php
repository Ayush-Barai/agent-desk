<div>
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Support Targets</h3>
        <p class="text-sm text-gray-500 mt-1">Configure the target response and resolution times for support tickets.</p>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @if($saved)
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4">
                    <p class="text-sm text-green-800">Settings saved successfully.</p>
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Response Time Target (hours)</label>
                    <p class="text-xs text-gray-500 mb-1">The target number of hours for an agent to send the first response to a new ticket.</p>
                    <input wire:model="first_response_hours" type="number" min="1" max="720"
                        class="mt-1 w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('first_response_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Resolution Time Target (hours)</label>
                    <p class="text-xs text-gray-500 mb-1">The target number of hours to fully resolve a ticket from the time it was created.</p>
                    <input wire:model="resolution_hours" type="number" min="1" max="2160"
                        class="mt-1 w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('resolution_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                    Save Settings
                </button>
            </form>
        </div>
    </div>
</div>
