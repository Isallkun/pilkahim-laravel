@php
    // Sidebar items "Kelola Kandidat" & "Kelola DPT" konteks-nya per-election (nested route).
    // Strategi link: route ke election yang sedang dilihat (pakai param URL); kalau tidak ada,
    // fallback ke election aktif; kalau itu juga tidak ada, ke election terakhir; final fallback elections.index.
    $sidebarElection = request()->route('election');
    if (!$sidebarElection instanceof \App\Models\Election) {
        $sidebarElection = \App\Models\Election::where('status', 'active')->latest('start_date')->first()
            ?? \App\Models\Election::latest('created_at')->first();
    }

    $candidatesUrl = $sidebarElection
        ? route('admin.elections.candidates.index', $sidebarElection)
        : route('admin.elections.index');
    $dptUrl = $sidebarElection
        ? route('admin.dpt.index', $sidebarElection)
        : route('admin.elections.index');
@endphp

{{-- Sidebar konten — dipakai untuk desktop & mobile drawer --}}
<div class="flex h-full flex-col">
    {{-- Brand --}}
    <div class="mb-8 px-6 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-[#E5A100] flex items-center justify-center text-white font-bold text-lg leading-none">A</div>
        <div>
            <div class="font-display text-[20px] font-[800] text-[#E5A100] leading-none">Arutala</div>
            <div class="font-bold text-[10px] text-[#8E8676] tracking-wider leading-tight">ADMIN PANEL</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto">
        <a href="{{ route('admin.dashboard') }}"
           class="a-side-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">dashboard</span>
            Dashboard
        </a>

        <a href="{{ route('admin.elections.index') }}"
           class="a-side-link {{ request()->routeIs('admin.elections.index') || request()->routeIs('admin.elections.create') || request()->routeIs('admin.elections.edit') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">how_to_vote</span>
            Event Pemilihan
        </a>

        <a href="{{ $candidatesUrl }}"
           class="a-side-link {{ request()->routeIs('admin.elections.candidates.*') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">groups</span>
            Kelola Kandidat
        </a>

        <a href="{{ $dptUrl }}"
           class="a-side-link {{ request()->routeIs('admin.voters.*') || request()->routeIs('admin.dpt.*') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">badge</span>
            Kelola DPT
        </a>

        <a href="{{ route('admin.audit-logs.index') }}"
           class="a-side-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">history</span>
            Audit Log
        </a>

        <a href="{{ route('admin.reports.index') }}"
           class="a-side-link {{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">summarize</span>
            Laporan
        </a>

        <a href="{{ route('admin.settings.index') }}"
           class="a-side-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
            <span class="material-symbols-outlined text-[20px]">settings</span>
            Pengaturan
        </a>
    </nav>

    {{-- Profile / User card --}}
    <div class="mt-auto px-6 pt-4">
        <div class="flex items-center gap-3 bg-[#FAF8F4] p-3 rounded-xl border border-[#F2EFE8]">
            <div class="w-10 h-10 rounded-full bg-[#E5A100]/20 flex items-center justify-center text-[#E5A100] shrink-0">
                <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="font-bold text-[14px] text-on-background truncate">{{ Auth::user()?->name ?? 'KPU HIMA' }}</p>
                <p class="text-[12px] text-on-surface-variant truncate">Administrator</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" title="Logout" class="text-[#5C5648] hover:text-[#BA1A1A] transition-colors p-1">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                </button>
            </form>
        </div>
    </div>
</div>
