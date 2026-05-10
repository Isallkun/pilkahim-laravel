@props([
    'type' => 'draft',
])

@php
    $classes = match ($type) {
        'draft' => 'bg-gray-100 text-gray-800',
        'active' => 'bg-green-100 text-green-800',
        'finished' => 'bg-blue-100 text-blue-800',
        'private' => 'bg-red-100 text-red-800',
        'public' => 'bg-primary-100 text-primary-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ' . $classes]) }}>
    {{ $slot }}
</span>
