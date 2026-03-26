<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-gray-900">Knowledge Base Articles</h3>
        @unless($showForm)
            <button wire:click="openCreate" type="button"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Article
            </button>
        @endunless
    </div>

    @if($showForm)
        <div class="bg-white border border-gray-100 rounded-2xl shadow-lg ring-1 ring-black/5 mb-6 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">{{ $editingId ? 'Edit Article' : 'Create Article' }}</h4>
            </div>
            <div class="p-6 bg-white">
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Title</label>
                        <input wire:model="title" type="text" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-shadow">
                        @error('title') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Excerpt</label>
                        <input wire:model="excerpt" type="text" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-shadow" placeholder="Optional short summary">
                        @error('excerpt') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Body</label>
                        <textarea wire:model="body" rows="10" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono transition-shadow"></textarea>
                        @error('body') <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="inline-flex items-center cursor-pointer">
                            <input wire:model="is_published" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 shadow-sm transition-colors">
                            <span class="ml-3 text-sm font-semibold text-gray-900">Published</span>
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
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Excerpt</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($articles as $article)
                        <tr wire:key="kb-{{ $article->id }}" class="hover:bg-indigo-50/40 transition-colors duration-200 group">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $article->title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ Str::limit($article->excerpt ?? '', 60) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $article->is_published ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-400/20' }}">
                                    {{ $article->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button wire:click="openEdit('{{ $article->id }}')" class="font-semibold text-indigo-600 hover:text-indigo-900 transition-colors" title="Edit Article">
                                        Edit
                                    </button>
                                    <button wire:click="deleteArticle('{{ $article->id }}')" wire:confirm="Are you sure you want to delete this article? This action cannot be undone." class="font-semibold text-red-600 hover:text-red-900 transition-colors" title="Delete Article">
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900">No articles found</p>
                                    <p class="text-sm mt-1 text-gray-500">Create a new knowledge base article to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($articles->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $articles->links() }}
        </div>
        @endif
    </div>
</div>
