{{-- Desktop topbar — breadcrumb + status pill + profile --}}
@php
    // Status election aktif untuk dipajang sebagai pill di topbar.
    $activeElectionTopbar = \App\Models\Election::where('status', 'active')->latest('start_date')->first();
@endphp
<header class="hidden md:flex h-[64px] bg-white border-b border-[#F2EFE8] items-center justify-between px-8 sticky top-0 z-30">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-[14px] text-on-surface-variant">
        <span class="font-medium">Admin Panel</span>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="font-bold text-[#E5A100]">@yield('breadcrumb', 'Dashboard')</span>
    </div>

    {{-- Right cluster --}}
    <div class="flex items-center gap-4">
        {{-- Election status pill --}}
        @if($activeElectionTopbar)
            <div class="flex items-center gap-2 bg-[#FEF9E7] px-3 py-1.5 rounded-full border border-[#E5A100]/30">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#E5A100] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#E5A100]"></span>
                </span>
                <span class="font-bold text-[12px] text-[#B45309] tracking-wider">VOTING AKTIF</span>
            </div>
        @else
            <div class="flex items-center gap-2 bg-[#F4EDE3] px-3 py-1.5 rounded-full border border-[#D5C4AD]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#8E8676]"></span>
                <span class="font-bold text-[12px] text-[#5C5648] tracking-wider">TIDAK ADA EVENT AKTIF</span>
            </div>
        @endif

        {{-- Avatar --}}
        <div class="w-[32px] h-[32px] rounded-full bg-gradient-to-tr from-[#E5A100] to-[#FFE083] border-2 border-white shadow-sm flex items-center justify-center text-white font-bold text-sm">
            {{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 1)) }}
        </div>
    </div>
</header>
