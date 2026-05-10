@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $baseClasses = 'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variantClasses = match ($variant) {
        'primary' => 'bg-primary-500 hover:bg-primary-600 text-white focus:ring-primary-500',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800 focus:ring-gray-500',
        'danger' => 'bg-red-500 hover:bg-red-600 text-white focus:ring-red-500',
        default => 'bg-primary-500 hover:bg-primary-600 text-white focus:ring-primary-500',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $variantClasses]) }}>
    {{ $slot }}
</button>
