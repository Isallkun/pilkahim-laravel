@php
    // Same logic as sidebar: link DPT ke election yang relevan.
    $bnavElection = request()->route('election');
    if (!$bnavElection instanceof \App\Models\Election) {
        $bnavElection = \App\Models\Election::where('status', 'active')->latest('start_date')->first()
            ?? \App\Models\Election::latest('created_at')->first();
    }
    $bnavDptUrl = $bnavElection
        ? route('admin.dpt.index', $bnavElection)
        : route('admin.elections.index');
@endphp

{{-- Mobile bottom nav — admin --}}
<nav class="md:hidden fixed bottom-0 left-0 w-full z-30 bg-white flex justify-around items-center px-4 py-3 border-t border-[#E5E0D5] shadow-[0_-4px_20px_0_rgba(229,161,0,0.10)]">
    <a href="{{ route('admin.dashboard') }}"
       class="a-bnav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">dashboard</span>
        <span class="text-xs mt-1 {{ request()->routeIs('admin.dashboard') ? 'font-bold' : '' }}">Dashboard</span>
    </a>

    <a href="{{ route('admin.elections.index') }}"
       class="a-bnav-link {{ request()->routeIs('admin.elections.*') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">how_to_vote</span>
        <span class="text-xs mt-1 {{ request()->routeIs('admin.elections.*') ? 'font-bold' : '' }}">Event</span>
    </a>

    <a href="{{ $bnavDptUrl }}"
       class="a-bnav-link {{ request()->routeIs('admin.voters.*') || request()->routeIs('admin.dpt.*') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">badge</span>
        <span class="text-xs mt-1 {{ request()->routeIs('admin.voters.*') || request()->routeIs('admin.dpt.*') ? 'font-bold' : '' }}">DPT</span>
    </a>

    <a href="{{ route('admin.reports.index') }}"
       class="a-bnav-link {{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}">
        <span class="material-symbols-outlined">summarize</span>
        <span class="text-xs mt-1 {{ request()->routeIs('admin.reports.*') ? 'font-bold' : '' }}">Laporan</span>
    </a>
</nav>
