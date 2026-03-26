<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-gray-900">Macros</h3>
        @unless($showForm)
            <button wire:click="openCreate" type="button"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Macro
            </button>
        @endunless
    </div>

    @if($showForm)
        <div class="bg-white border border-gray-100 rounded-2xl shadow-lg ring-1 ring-black/5 mb-6 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">{{ $editingId ? 'Edit Macro' : 'Create Macro' }}</h4>
            </div>
            <div class="p-6 bg-white">
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Title</label>
                        <input wire:model="title" type="text" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-shadow">
                        @error('title') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Body</label>
                        <textarea wire:model="body" rows="6" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono transition-shadow"></textarea>
                        @error('body') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input wire:model="is_active" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 shadow-sm transition-colors">
                            <span class="ml-3 text-sm font-semibold text-gray-900">Active</span>
                        </label>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 transition-all shadow-sm">Save Complete</button>
                        <button wire:click="cancel" type="button" class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2 transition-all shadow-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden ring-1 ring-black/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-200">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Title</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Body Preview</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($macros as $macro)
                        <tr wire:key="macro-{{ $macro->id }}" class="hover:bg-indigo-50/40 transition-colors duration-200 group">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $macro->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ Str::limit($macro->body, 80) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $macro->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-400/20' }}">
                                    {{ $macro->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button wire:click="openEdit('{{ $macro->id }}')" class="font-semibold text-indigo-600 hover:text-indigo-900 transition-colors" title="Edit Macro">
                                        Edit
                                    </button>
                                    <button wire:click="deleteMacro('{{ $macro->id }}')" wire:confirm="Are you sure you want to delete this macro? This action cannot be undone." class="font-semibold text-red-600 hover:text-red-900 transition-colors" title="Delete Macro">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900">No macros found</p>
                                    <p class="text-sm mt-1 text-gray-500">Create a new macro to save canned responses for your team.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($macros->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $macros->links() }}
        </div>
        @endif
    </div>
</div>
