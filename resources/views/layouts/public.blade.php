<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'E-Vote') - E-Vote Arutala IAIC Pasuruan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-full flex flex-col">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="h-8 w-8 text-primary-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        <span class="text-lg font-semibold text-gray-900">E-Vote Arutala</span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <main class="flex-1">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-200 bg-white py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-sm text-gray-500">E-Vote Arutala IAIC Pasuruan Lead 2026</p>
                <p class="mt-1 text-xs text-gray-400">&copy; {{ date('Y') }} Arutala. All rights reserved.</p>
            </div>
        </footer>
    </div>

    @stack('scripts')
    @livewireScripts
</body>
</html>
