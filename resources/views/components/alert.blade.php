@props(['type' => 'info'])

@php
    $colorClasses = match ($type) {
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        default => 'bg-blue-50 border-blue-200 text-blue-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg p-4 shadow-sm $colorClasses"]) }} role="alert">
    {{ $slot }}
</div>