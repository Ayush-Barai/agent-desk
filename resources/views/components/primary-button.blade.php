<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white hover:bg-indigo-700 hover:-translate-y-0.5 shadow-sm hover:shadow-md focus:bg-indigo-700 active:bg-indigo-800 active:translate-y-0 active:shadow-sm focus:outline-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200']) }}>
    {{ $slot }}
</button>
