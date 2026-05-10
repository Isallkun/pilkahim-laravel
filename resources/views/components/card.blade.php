@props([])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm p-6']) }}>
    {{ $slot }}
</div>
