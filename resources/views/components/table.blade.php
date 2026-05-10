@props([])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-gray-200']) }}>
    <table class="min-w-full divide-y divide-gray-200">
        @if (isset($head))
            <thead class="bg-gray-50">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-gray-200 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
