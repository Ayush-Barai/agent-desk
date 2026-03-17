<div>
    <form wire:submit="submit" class="space-y-6">
        <div>
            <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" id="subject" wire:model="subject"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Brief description of your issue">
            @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="categoryId" class="block text-sm font-medium text-gray-700">Category</label>
            <select id="categoryId" wire:model="categoryId"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Select a category (optional) —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('categoryId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" wire:model="description" rows="6"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Describe your issue in detail..."></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="attachments" class="block text-sm font-medium text-gray-700">Attachments</label>
            <input type="file" id="attachments" wire:model="attachments" multiple
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <div wire:loading wire:target="attachments" class="mt-1 text-sm text-gray-500">Uploading...</div>
            @error('attachments.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

            @if(count($attachments) > 0)
                <ul class="mt-3 space-y-2">
                    @foreach($attachments as $index => $attachment)
                        <li class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2 bg-gray-50">
                            <span class="text-sm font-medium text-gray-700 truncate mr-4" title="{{ $attachment->getClientOriginalName() }}">
                                {{ Str::limit($attachment->getClientOriginalName(), 40) }}
                            </span>
                            <button type="button" wire:click="removeAttachment({{ $index }})" 
                                class="shrink-0 text-red-500 hover:text-red-700 transition" 
                                aria-label="Remove {{ $attachment->getClientOriginalName() }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <span wire:loading.remove wire:target="submit">Create Ticket</span>
                <span wire:loading wire:target="submit">Creating...</span>
            </button>

            <a href="{{ route('requester.tickets.index') }}" wire:navigate
                class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</div>
