<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — Arutala IAIC Pasuruan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary-fixed-dim": "#eec200",
                        "primary": "#7e5700",
                        "error": "#ba1a1a",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-highest": "#e8e1d8",
                        "background": "#fff8f1",
                        "tertiary-container": "#d1aa00",
                        "surface-container": "#f4ede3",
                        "on-primary": "#ffffff",
                        "secondary-fixed": "#ffe173",
                        "on-primary-fixed-variant": "#5f4100",
                        "surface-variant": "#e8e1d8",
                        "on-primary-container": "#593c00",
                        "on-background": "#1e1b16",
                        "inverse-on-surface": "#f7f0e6",
                        "outline-variant": "#d5c4ad",
                        "on-tertiary-fixed": "#231b00",
                        "on-error": "#ffffff",
                        "error-container": "#ffdad6",
                        "on-secondary-container": "#715d00",
                        "surface-container-low": "#faf3e9",
                        "secondary-container": "#fdd73b",
                        "on-secondary-fixed-variant": "#554500",
                        "on-secondary-fixed": "#221b00",
                        "on-tertiary-container": "#504000",
                        "secondary": "#705d00",
                        "outline": "#837561",
                        "on-surface": "#1e1b16",
                        "primary-container": "#e5a100",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed": "#281900",
                        "primary-fixed-dim": "#ffba36",
                        "surface": "#fff8f1",
                        "primary-fixed": "#ffdeac",
                        "on-secondary": "#ffffff",
                        "surface-tint": "#7e5700",
                        "on-tertiary-fixed-variant": "#574500",
                        "on-surface-variant": "#514533",
                        "on-error-container": "#93000a",
                        "inverse-surface": "#33302a",
                        "inverse-primary": "#ffba36",
                        "tertiary-fixed": "#ffe083",
                        "tertiary": "#735c00",
                        "surface-bright": "#fff8f1",
                        "secondary-fixed-dim": "#e8c426",
                        "surface-dim": "#e0d9d0",
                        "surface-container-high": "#eee7de"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px",
                        "2xl": "1rem",
                        "3xl": "1.5rem"
                    },
                    "spacing": {
                        "gutter": "1rem",
                        "margin-mobile": "1.25rem",
                        "lg": "1.5rem",
                        "margin-desktop": "5rem",
                        "xs": "0.25rem",
                        "sm": "0.5rem",
                        "md": "1rem",
                        "xl": "2.5rem"
                    },
                    "fontFamily": {
                        "display-mobile": ["Baloo 2"],
                        "display": ["Baloo 2"],
                        "h1-mobile": ["Baloo 2"],
                        "h2": ["Baloo 2"],
                        "body-md": ["Baloo 2"],
                        "body-lg": ["Baloo 2"],
                        "label-caps": ["Baloo 2"],
                        "h1": ["Baloo 2"]
                    },
                    "fontSize": {
                        "display-mobile": ["42px", { "lineHeight": "1.1", "fontWeight": "800" }],
                        "display": ["64px", { "lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "800" }],
                        "h1-mobile": ["28px", { "lineHeight": "1.2", "fontWeight": "700" }],
                        "h2": ["24px", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "1.7", "fontWeight": "400" }],
                        "body-lg": ["18px", { "lineHeight": "1.7", "fontWeight": "400" }],
                        "label-caps": ["14px", { "lineHeight": "1.0", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "h1": ["36px", { "lineHeight": "1.2", "fontWeight": "700" }]
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Baloo 2', system-ui, sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glow-shadow {
            box-shadow: 0 10px 25px -5px rgba(229, 161, 0, 0.10), 0 8px 10px -6px rgba(229, 161, 0, 0.05);
        }
        .glow-shadow-hover:hover {
            box-shadow: 0 20px 25px -5px rgba(229, 161, 0, 0.15), 0 8px 10px -6px rgba(229, 161, 0, 0.10);
        }
        /* Sidebar nav link */
        .a-side-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: #514533;
            border-left: 3px solid transparent;
            font-weight: 500;
            font-size: 14px;
            transition: background-color .15s, color .15s, border-color .15s;
        }
        .a-side-link:hover { background: #faf3e9; color: #7e5700; }
        .a-side-link.is-active {
            background: #FEF9E7;
            border-left-color: #E5A100;
            color: #B45309;
            font-weight: 700;
        }
        .a-side-link.is-active .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
        /* Mobile bottom-nav */
        .a-bnav-link {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #514533;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            transition: color .15s, background-color .15s;
        }
        .a-bnav-link:hover { color: #7e5700; }
        .a-bnav-link.is-active {
            background: #fdd73b;
            color: #715d00;
        }
        .a-bnav-link.is-active .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    @stack('head')
</head>
<body class="bg-[#FFFCF5] text-on-background min-h-screen flex" x-data="{ sidebarOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/40 md:hidden"
         @click="sidebarOpen = false"
         style="display: none;"></div>

    {{-- Mobile drawer sidebar --}}
    <aside x-show="sidebarOpen"
           x-transition:enter="transition ease-in-out duration-300 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in-out duration-300 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-[260px] bg-white md:hidden shadow-xl"
           style="display: none;">
        @include('layouts.partials.arutala-admin-sidebar')
    </aside>

    {{-- Desktop sidebar --}}
    <aside class="hidden md:flex flex-col w-[240px] bg-white border-r border-[#F2EFE8] h-screen sticky top-0 py-6 z-40 shrink-0">
        @include('layouts.partials.arutala-admin-sidebar')
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Mobile top app-bar --}}
        <header class="md:hidden flex justify-between items-center w-full px-gutter h-16 bg-white/80 backdrop-blur-md text-[#E5A100] sticky top-0 shadow-sm z-30 border-b border-[#F2EFE8]">
            <button @click="sidebarOpen = true" class="-ml-2 p-2 text-[#5C5648] hover:text-[#E5A100] transition-colors" aria-label="Buka menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="font-display text-h1-mobile text-[#E5A100]">Arutala</div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="text-[#5C5648] hover:text-[#E5A100] transition-colors p-2 -mr-2" aria-label="Logout">
                    <span class="material-symbols-outlined">logout</span>
                </button>
            </form>
        </header>

        {{-- Desktop topbar --}}
        @include('layouts.partials.arutala-admin-topbar')

        {{-- Flash messages --}}
        @if (session('success') || session('error'))
            <div class="px-6 lg:px-8 pt-4">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-2 rounded-2xl bg-[#ECFDF5] border border-[#A7F3D0] p-4 flex items-start gap-3">
                        <span class="material-symbols-outlined fill text-[#059669]">check_circle</span>
                        <p class="flex-1 text-sm text-[#065F46] font-medium">{{ session('success') }}</p>
                        <button @click="show = false" class="text-[#059669] hover:text-[#047857]"><span class="material-symbols-outlined text-[18px]">close</span></button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-2 rounded-2xl bg-[#FEF2F2] border border-[#FECACA] p-4 flex items-start gap-3">
                        <span class="material-symbols-outlined fill text-[#DC2626]">error</span>
                        <p class="flex-1 text-sm text-[#991B1B] font-medium">{{ session('error') }}</p>
                        <button @click="show = false" class="text-[#DC2626] hover:text-[#B91C1C]"><span class="material-symbols-outlined text-[18px]">close</span></button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Page content (mendukung Livewire `{{ $slot }}` dan Blade `@extends/@section('content')`) --}}
        <main class="flex-1 p-6 lg:p-[32px] max-w-[1280px] w-full pb-24 md:pb-xl">
            {{ $slot ?? '' }}
            @hasSection('content')
                @yield('content')
            @endif
        </main>
    </div>

    {{-- Mobile bottom nav --}}
    @include('layouts.partials.arutala-admin-mobile-nav')

    @stack('scripts')
    @livewireScripts
</body>
</html>
