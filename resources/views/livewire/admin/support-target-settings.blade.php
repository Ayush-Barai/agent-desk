<div class="max-w-4xl space-y-6">
    <div class="mb-4">
        <h3 class="text-xl font-bold text-gray-900">Support Targets</h3>
        <p class="text-sm text-gray-500 mt-2 font-medium">Configure the target response and resolution times for support tickets.</p>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
        <div class="p-8">
            @if($saved)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-4" class="mb-6 rounded-xl bg-emerald-50 border border-emerald-100 p-4 flex items-center gap-3">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm font-bold text-emerald-800 tracking-wide">Settings saved successfully.</p>
                </div>
            @endif

            <form wire:submit="save" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Response Target</h4>
                        </div>
                        <p class="text-xs font-medium text-gray-500 mb-4 h-10">The target number of hours for an agent to send the first response to a new ticket.</p>
                        
                        <div class="relative">
                            <input wire:model="first_response_hours" type="number" min="1" max="720"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg font-bold py-3 px-4 transition-shadow bg-white pr-16 text-gray-900">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <span class="text-gray-500 font-medium text-sm uppercase tracking-wider">Hours</span>
                            </div>
                        </div>
                        @error('first_response_hours') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Resolution Target</h4>
                        </div>
                        <p class="text-xs font-medium text-gray-500 mb-4 h-10">The target number of hours to fully resolve a ticket from the time it was created.</p>
                        
                        <div class="relative">
                            <input wire:model="resolution_hours" type="number" min="1" max="2160"
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg font-bold py-3 px-4 transition-shadow bg-white pr-16 text-gray-900">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <span class="text-gray-500 font-medium text-sm uppercase tracking-wider">Hours</span>
                            </div>
                        </div>
                        @error('resolution_hours') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md hover:-translate-y-0.5 w-full sm:w-auto justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
