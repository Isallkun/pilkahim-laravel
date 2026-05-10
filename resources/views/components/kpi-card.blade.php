@props([
    'label',
    'value',
    'icon' => null,
    'color' => 'primary',
])

@php
    $borderColor = match ($color) {
        'primary' => 'border-l-primary-500',
        'green' => 'border-l-green-500',
        'blue' => 'border-l-blue-500',
        'red' => 'border-l-red-500',
        'yellow' => 'border-l-yellow-500',
        'purple' => 'border-l-purple-500',
        default => 'border-l-primary-500',
    };

    $iconColor = match ($color) {
        'primary' => 'text-primary-500',
        'green' => 'text-green-500',
        'blue' => 'text-blue-500',
        'red' => 'text-red-500',
        'yellow' => 'text-yellow-500',
        'purple' => 'text-purple-500',
        default => 'text-primary-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm p-6 border-l-4 ' . $borderColor]) }}>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
            <p class="mt-1 text-sm font-medium text-gray-500">{{ $label }}</p>
        </div>
        @if ($icon)
            <div class="{{ $iconColor }}">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
