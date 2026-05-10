@extends('layouts.arutala')

@section('title', 'Live Count — ' . $election->name)

@push('head')
<style>
    .glow-shadow { box-shadow: 0 4px 20px rgba(229, 161, 0, 0.15); }
    .pulse-red { animation: pulse-red 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse-red {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }
    .bar-fill { transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
@endpush

@section('body')
@include('layouts.partials.arutala-header')

<main class="flex-grow w-full max-w-[1200px] mx-auto px-6 sm:px-8 py-10 md:py-14 flex flex-col gap-12"
      data-election-id="{{ $election->id }}"
      data-end-date="{{ $electionEndDate ?? '' }}">

    {{-- HEADER SECTION --}}
    <section class="flex flex-col gap-5 items-center text-center">
        <div class="inline-flex items-center gap-2 bg-[#FFFBEA] border border-[#D97706] rounded-full px-4 py-1.5">
            <span class="w-2 h-2 rounded-full bg-[#BA1A1A] pulse-red"></span>
            <span class="font-bold text-[12px] text-[#D97706] uppercase tracking-[0.12em]">Live Status</span>
        </div>

        <h1 class="font-display text-[32px] sm:text-[42px] md:text-[56px] font-extrabold text-[#7e5700] leading-[1.1]">
            Perolehan <span class="text-[#E5A100]">Suara Sementara</span>
        </h1>
        <p class="text-sm sm:text-base text-[#5C5648] max-w-xl">
            {{ $election->name }}
        </p>

        {{-- 3 stat cards --}}
        <div class="flex flex-wrap justify-center gap-3 sm:gap-4 mt-2">
            <div class="bg-white border border-[#E5E0D5] rounded-2xl px-5 py-3 flex flex-col items-center min-w-[120px] shadow-sm">
                <span class="text-xs text-[#8E8676] font-medium">Total Suara</span>
                <span id="stat-total-votes" class="font-bold text-[28px] sm:text-[32px] text-[#7e5700] tabular-nums leading-tight">{{ number_format($totalVotes) }}</span>
            </div>
            <div class="bg-white border border-[#E5E0D5] rounded-2xl px-5 py-3 flex flex-col items-center min-w-[120px] shadow-sm">
                <span class="text-xs text-[#8E8676] font-medium">Kandidat</span>
                <span class="font-bold text-[28px] sm:text-[32px] text-[#7e5700] tabular-nums leading-tight">{{ $candidateCount }}</span>
            </div>
            @if($election->show_countdown)
                <div class="bg-white border border-[#E5E0D5] rounded-2xl px-5 py-3 flex flex-col items-center min-w-[140px] shadow-sm">
                    <span class="text-xs text-[#8E8676] font-medium">Sisa Waktu</span>
                    <span id="stat-time-remaining" class="font-bold text-[24px] sm:text-[28px] text-[#7e5700] tabular-nums leading-tight">—</span>
                </div>
            @endif
        </div>
    </section>

    {{-- VOTER TURNOUT --}}
    <section class="bg-white rounded-2xl border border-[#E5E0D5] p-6 sm:p-8 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <span class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24]">Partisipasi Pemilih</span>
            <span id="turnout-percentage" class="font-bold text-[20px] sm:text-[24px] text-[#E5A100] tabular-nums">{{ $turnoutPercentage }}%</span>
        </div>
        <div class="w-full bg-[#F4EDE3] h-4 rounded-full overflow-hidden">
            <div id="turnout-bar" class="bg-gradient-to-r from-[#E5A100] to-[#FCD34D] h-full rounded-full bar-fill"
                 style="width: {{ $turnoutPercentage }}%;"></div>
        </div>
        <p id="turnout-detail" class="text-sm text-[#5C5648] mt-3">
            <span class="font-bold tabular-nums">{{ number_format($totalVotes) }}</span> dari
            <span class="font-bold tabular-nums">{{ number_format($totalEligible) }}</span> pemilih terdaftar telah berpartisipasi.
        </p>
    </section>

    {{-- CANDIDATE CARDS --}}
    <section id="candidates-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Diisi via JS render. Initial state di-render server-side untuk no-JS fallback. --}}
        @include('results._candidate-cards', ['candidates' => $candidateResults])
    </section>

    {{-- LIVE UPDATE BADGE + DISCLAIMER --}}
    <section class="flex flex-col gap-4 items-center text-center mt-2">
        <div class="bg-white border border-[#E5E0D5] rounded-full px-5 py-2.5 inline-flex items-center gap-2 shadow-sm">
            <span id="sync-icon" class="material-symbols-outlined text-[#E5A100] animate-spin" style="font-size: 18px; animation-duration: 3s;">sync</span>
            <span class="text-sm text-[#5C5648]">Data diperbarui setiap <strong>5 detik</strong> secara otomatis</span>
        </div>

        <div class="bg-[#FFFBEA] border border-[#FDE68A] rounded-2xl px-5 py-4 max-w-2xl">
            <div class="flex items-start gap-2">
                <span class="material-symbols-outlined text-[#B45309] text-[20px] mt-0.5 shrink-0">info</span>
                <p class="text-xs sm:text-sm text-[#5C5648] text-left leading-relaxed">
                    <strong class="text-[#B45309]">Penting:</strong> Angka di atas adalah perolehan suara <strong>sementara</strong> (Live Count) dan <strong>bukan hasil akhir resmi</strong>. Hasil final akan diumumkan setelah proses rekapitulasi selesai dan disahkan oleh panitia pemilihan.
                </p>
            </div>
        </div>
    </section>
</main>

@include('layouts.partials.arutala-footer')
@include('layouts.partials.arutala-mobile-nav')

@endsection

@push('scripts')
<script>
(function () {
    const root = document.querySelector('main[data-election-id]');
    if (!root) return;

    const electionId = root.dataset.electionId;
    const endDateStr = root.dataset.endDate;
    const endDate = endDateStr ? new Date(endDateStr) : null;

    const liveUrl = "{{ route('results.public.live', $election) }}";

    // ===== Countdown timer =====
    const timeEl = document.getElementById('stat-time-remaining');
    function pad(n) { return String(n).padStart(2, '0'); }

    function tickCountdown() {
        if (!timeEl) return;
        if (!endDate) { timeEl.textContent = '—'; return; }
        const diff = endDate - new Date();
        if (diff <= 0) {
            timeEl.textContent = 'Selesai';
            timeEl.classList.add('text-red-700');
            return;
        }
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const mins = Math.floor((diff % 3600000) / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        if (days >= 1) {
            timeEl.textContent = `${days}h ${pad(hours)}j`;
        } else {
            timeEl.textContent = `${pad(hours)}:${pad(mins)}:${pad(secs)}`;
        }
    }
    tickCountdown();
    setInterval(tickCountdown, 1000);

    // ===== Polling =====
    const totalVotesEl = document.getElementById('stat-total-votes');
    const turnoutPctEl = document.getElementById('turnout-percentage');
    const turnoutBarEl = document.getElementById('turnout-bar');
    const turnoutDetailEl = document.getElementById('turnout-detail');
    const grid = document.getElementById('candidates-grid');
    const syncIcon = document.getElementById('sync-icon');

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n);
    }

    function renderCardHtml(c, index) {
        const isLeading = index === 0 && c.votes > 0;
        const photo = c.photo_url
            ? `<img src="${escapeHtml(c.photo_url)}" alt="${escapeHtml(c.name)}" class="w-full h-full object-cover">`
            : `<div class="w-full h-full flex items-center justify-center text-[#8E8676]"><span class="material-symbols-outlined" style="font-size:80px;">person</span></div>`;
        const num = String(c.sort_order).padStart(2, '0');
        const positionLabel = `Posisi #${index + 1}`;

        const cardClasses = isLeading
            ? 'bg-white rounded-3xl border-2 border-[#E5A100] p-4 flex flex-col relative overflow-hidden glow-shadow transition-all hover:-translate-y-1'
            : 'bg-white rounded-3xl border border-[#E5E0D5] p-4 flex flex-col relative overflow-hidden transition-all hover:-translate-y-1 hover:shadow-md';

        const leadingBadge = isLeading
            ? `<div class="absolute top-0 right-0 bg-[#E5A100] text-white font-bold text-[11px] tracking-[0.12em] px-3 py-1.5 rounded-bl-2xl z-10 inline-flex items-center gap-1">
                   <span class="material-symbols-outlined fill text-[14px]">workspace_premium</span>SEDANG MEMIMPIN
               </div>`
            : '';

        const numberBadge = isLeading
            ? `<div class="absolute bottom-2 left-2 bg-[#FFFBEA] w-12 h-12 rounded-full flex items-center justify-center border-2 border-[#E5A100] shadow-md z-10">
                   <span class="font-bold text-[18px] text-[#D97706]">${num}</span>
               </div>`
            : `<div class="absolute bottom-2 left-2 bg-white w-12 h-12 rounded-full flex items-center justify-center border border-[#E5E0D5] shadow-sm z-10">
                   <span class="font-bold text-[18px] text-[#5C5648]">${num}</span>
               </div>`;

        const positionPill = isLeading
            ? `<span class="bg-[#FFFBEA] text-[#D97706] border border-[#FDE68A] font-bold text-[11px] tracking-[0.1em] px-3 py-1 rounded-full uppercase">${positionLabel}</span>`
            : `<span class="bg-[#F4EDE3] text-[#5C5648] font-bold text-[11px] tracking-[0.1em] px-3 py-1 rounded-full uppercase">${positionLabel}</span>`;

        const votesColor = isLeading ? 'text-[#D97706]' : 'text-[#2D2A24]';
        const barColor = isLeading ? 'bg-gradient-to-r from-[#E5A100] to-[#FCD34D]' : 'bg-[#D5C4AD]';
        const pctColor = isLeading ? 'text-[#D97706]' : 'text-[#5C5648]';

        return `
            <article class="${cardClasses}">
                ${leadingBadge}
                <div class="relative w-full aspect-[4/5] rounded-2xl overflow-hidden bg-[#F4EDE3] mb-4">
                    ${photo}
                    ${numberBadge}
                </div>
                <div class="flex flex-col items-center text-center gap-1 mb-4">
                    ${positionPill}
                    <h3 class="font-bold text-[20px] text-[#2D2A24] mt-2">${escapeHtml(c.name)}</h3>
                    <p class="text-sm text-[#8E8676]">Nomor Urut ${num}</p>
                </div>
                <div class="mt-auto flex flex-col gap-2">
                    <div class="flex justify-between items-end">
                        <span class="text-sm text-[#5C5648] font-medium">Suara</span>
                        <span class="font-bold text-[28px] ${votesColor} tabular-nums leading-none">${fmt(c.votes)}</span>
                    </div>
                    <div class="w-full bg-[#F4EDE3] h-3 rounded-full overflow-hidden">
                        <div class="${barColor} h-full rounded-full bar-fill" style="width: ${c.percentage}%;"></div>
                    </div>
                    <div class="text-right font-bold text-[18px] ${pctColor} tabular-nums">${c.percentage.toFixed(1)}%</div>
                </div>
            </article>
        `;
    }

    function applyData(data) {
        totalVotesEl.textContent = fmt(data.totalVotes);
        turnoutPctEl.textContent = data.turnoutPercentage + '%';
        turnoutBarEl.style.width = data.turnoutPercentage + '%';
        turnoutDetailEl.innerHTML = `<span class="font-bold tabular-nums">${fmt(data.totalVotes)}</span> dari <span class="font-bold tabular-nums">${fmt(data.totalEligible)}</span> pemilih terdaftar telah berpartisipasi.`;
        grid.innerHTML = data.candidateResults.map((c, i) => renderCardHtml(c, i)).join('');
    }

    let isPolling = false;
    async function poll() {
        if (isPolling) return;
        isPolling = true;
        syncIcon.style.animationDuration = '0.8s'; // speed up while fetching
        try {
            const res = await fetch(liveUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Polling failed: ' + res.status);
            const data = await res.json();
            applyData(data);
        } catch (e) {
            // Silent fail — keep showing last data
            console.warn('Live poll error:', e);
        } finally {
            syncIcon.style.animationDuration = '3s';
            isPolling = false;
        }
    }

    setInterval(poll, 5000);
})();
</script>
@endpush
