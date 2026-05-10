@extends('layouts.arutala')

@section('title', 'Pilih Kandidat')

@section('body')

{{-- Voter Header (custom — tidak pakai partials.arutala-header karena halaman voting tidak butuh nav scroll) --}}
<header class="bg-white/95 backdrop-blur-md sticky top-0 w-full z-40 border-b border-[#F2EFE8]">
    <div class="flex justify-between items-center w-full px-6 sm:px-8 max-w-[1280px] mx-auto h-[72px]">
        <a href="{{ route('home') }}" class="font-display text-h1 text-[#E5A100]">Arutala</a>
        <div class="flex items-center gap-3">
            <span class="font-medium text-sm text-[#5C5648] hidden md:inline">
                Halo, <span class="font-semibold text-[#2D2A24]">{{ auth()->user()->name }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-white border border-[#E5E0D5] text-[#5C5648] hover:text-[#E5A100] hover:border-[#E5A100] font-medium text-sm px-4 py-2 rounded-full transition-colors">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</header>

<form method="POST" action="{{ route('vote.submit', $election) }}" id="vote-form">
    @csrf
    <input type="hidden" name="candidate_id" id="candidate_id" required>

    <main class="px-6 sm:px-8 max-w-[1280px] mx-auto py-8 md:py-12 flex flex-col gap-10">

        {{-- Page Title --}}
        <section class="text-center flex flex-col items-center gap-3">
            <span class="font-bold text-[12px] sm:text-[13px] text-[#735c00] tracking-[0.15em] uppercase">{{ $election->name }}</span>
            <h1 class="font-display text-[36px] sm:text-[48px] md:text-[56px] leading-[1.1] font-extrabold text-[#7e5700]">
                Pilih <span class="text-[#E5A100]">Pemimpin</span> Pilihanmu
            </h1>
            <p class="text-base sm:text-lg text-[#5C5648] max-w-2xl">
                Silakan pilih satu kandidat terbaik untuk memimpin {{ $election->name }}. Pilihan Anda menentukan masa depan organisasi kita.
            </p>
        </section>

        @if($election->show_countdown && $election->end_date && $election->end_date->isFuture())
            {{-- Election Countdown Banner --}}
            <section id="election-countdown"
                     data-end="{{ $election->end_date->toIso8601String() }}"
                     class="bg-gradient-to-br from-[#FFFBEA] to-[#FEF3C7] border-2 border-[#FCD34D] rounded-2xl px-5 sm:px-6 py-4 sm:py-5 max-w-4xl mx-auto w-full">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-white shadow-md flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined fill text-[#E5A100] text-[26px]">timer</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-[#8E8676] uppercase tracking-wider">Waktu pemilihan tersisa</p>
                            <p id="election-countdown-text" class="font-bold text-[24px] sm:text-[28px] text-[#7e5700] tabular-nums leading-tight">memuat…</p>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-10 bg-[#FCD34D]/60"></div>
                    <p class="text-xs sm:text-sm text-[#5C5648] text-center sm:text-left max-w-xs">
                        Pastikan Anda sudah memilih <strong>sebelum waktu habis</strong>. Suara setelah deadline tidak akan tercatat.
                    </p>
                </div>
            </section>
        @endif

        {{-- Info Strip --}}
        <section class="bg-[#FFFBEA] border border-[#FDE68A] rounded-2xl px-5 py-4 flex flex-col md:flex-row items-center justify-center gap-4 md:gap-8 max-w-4xl mx-auto w-full">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#E5A100] fill">lock</span>
                <span class="text-sm font-medium text-[#5C5648]">Suara Anda Rahasia</span>
            </div>
            <div class="hidden md:block w-px h-6 bg-[#FDE68A]"></div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#E5A100]">how_to_vote</span>
                <span class="text-sm font-medium text-[#5C5648]">Hanya bisa memilih SATU kali</span>
            </div>
            <div class="hidden md:block w-px h-6 bg-[#FDE68A]"></div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#E5A100]">verified</span>
                <span class="text-sm font-medium text-[#5C5648]">Terverifikasi sistem</span>
            </div>
        </section>

        {{-- Error / flash messages --}}
        @if (session('error'))
            <div class="max-w-4xl mx-auto w-full rounded-xl bg-red-50 border border-red-200 p-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-red-600">error</span>
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-4xl mx-auto w-full rounded-xl bg-red-50 border border-red-200 p-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-red-600">error</span>
                <p class="text-sm text-red-800">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Candidate Grid --}}
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($candidates as $candidate)
                <article class="candidate-card group bg-white border-2 border-[#E5E0D5] rounded-3xl p-4 flex flex-col gap-4 relative cursor-pointer transition-all duration-200 hover:border-[#FCD34D] hover:shadow-[0_8px_30px_0_rgba(229,161,0,0.1)]"
                         data-candidate-id="{{ $candidate->id }}"
                         data-candidate-name="{{ $candidate->name }}"
                         tabindex="0"
                         role="radio"
                         aria-checked="false"
                         onclick="selectCandidate({{ $candidate->id }})"
                         onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); selectCandidate({{ $candidate->id }}); }">

                    {{-- "PILIHAN ANDA" Banner (visible only when selected) --}}
                    <div class="selected-banner absolute -top-3 left-1/2 -translate-x-1/2 z-20 bg-[#E5A100] text-white px-4 py-1.5 rounded-full text-[11px] font-bold tracking-[0.15em] shadow-lg shadow-[#E5A100]/40 hidden items-center gap-1.5 whitespace-nowrap">
                        <span class="material-symbols-outlined text-[16px] fill">check_circle</span>
                        PILIHAN ANDA
                    </div>

                    {{-- Selection check (top-right) --}}
                    <div class="check-indicator absolute top-4 right-4 z-10 w-9 h-9 rounded-full border-2 border-[#E5E0D5] bg-white flex items-center justify-center transition-all">
                        <span class="material-symbols-outlined text-white fill text-[22px] check-icon hidden">check_circle</span>
                    </div>

                    {{-- Number badge (top-left) --}}
                    <div class="number-badge absolute top-4 left-4 z-10 bg-[#F4EDE3] text-[#5C5648] px-3 py-1 rounded-full font-bold text-[12px] tracking-[0.1em] transition-colors">
                        {{ str_pad($candidate->sort_order, 2, '0', STR_PAD_LEFT) }}
                    </div>

                    {{-- Photo --}}
                    <div class="aspect-[4/5] w-full rounded-2xl overflow-hidden bg-[#F4EDE3]">
                        @if ($candidate->photo_path)
                            <img src="{{ Storage::url($candidate->photo_path) }}"
                                 alt="Foto {{ $candidate->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[#8E8676]">
                                <span class="material-symbols-outlined" style="font-size: 80px;">person</span>
                            </div>
                        @endif
                    </div>

                    {{-- Name + Number --}}
                    <div class="flex flex-col gap-1 text-center px-2">
                        <h2 class="font-bold text-[20px] text-[#2D2A24]">{{ $candidate->name }}</h2>
                        <p class="text-sm text-[#8E8676] font-medium">Nomor Urut {{ $candidate->sort_order }}</p>
                    </div>

                    {{-- Visi tagline --}}
                    @if ($candidate->visi)
                        <div class="bg-[#FFFBEA] border border-[#FDE68A]/60 px-4 py-3 rounded-xl">
                            <p class="italic text-sm text-[#B45309] text-center leading-snug">
                                "{{ Str::limit($candidate->visi, 100) }}"
                            </p>
                        </div>
                    @endif

                    {{-- Detail button --}}
                    <button type="button"
                            onclick="event.stopPropagation(); openDetail({{ $candidate->id }})"
                            class="w-full h-12 rounded-full border border-[#E5E0D5] text-[#5C5648] font-semibold text-sm mt-auto hover:border-[#E5A100] hover:text-[#E5A100] hover:bg-[#FFFBEA] transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        Lihat Visi & Misi
                    </button>
                </article>
            @endforeach
        </section>

        {{-- Submit Section --}}
        <section class="flex flex-col items-center gap-4 mt-2 max-w-2xl mx-auto w-full pb-12">
            <label class="flex items-center gap-3 cursor-pointer bg-[#FAF8F4] px-5 py-4 rounded-2xl border border-[#E5E0D5] w-full hover:border-[#FCD34D] transition-colors">
                <input type="checkbox" id="declaration" required
                       class="w-5 h-5 rounded border-2 border-[#E5E0D5] accent-[#E5A100] focus:ring-2 focus:ring-[#E5A100] focus:ring-offset-0 shrink-0">
                <span class="text-sm text-[#2D2A24] leading-snug">
                    Saya menyatakan bahwa pilihan ini saya buat dengan <strong>sadar dan tanpa paksaan</strong>.
                </span>
            </label>

            <button type="submit"
                    id="submit-btn"
                    disabled
                    class="w-full md:w-auto md:min-w-[320px] h-[60px] bg-[#E5A100] hover:bg-[#D97706] text-white font-bold text-base sm:text-[17px] rounded-full shadow-[0_8px_24px_-4px_rgba(229,161,0,0.5)] transition-all flex items-center justify-center gap-2 disabled:bg-[#E5E0D5] disabled:text-[#8E8676] disabled:shadow-none disabled:cursor-not-allowed">
                <span class="material-symbols-outlined">send</span>
                <span>Kirim Suara Saya</span>
            </button>

            <p id="submit-hint" class="text-xs text-[#8E8676] text-center">
                Pilih kandidat dan setujui pernyataan untuk mengirim suara.
            </p>
        </section>
    </main>
</form>

{{-- Detail Modal --}}
<div id="detail-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" onclick="if (event.target === this) closeDetail()">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[85vh] overflow-hidden flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E5E0D5]">
            <h3 id="modal-title" class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24]"></h3>
            <button type="button" onclick="closeDetail()" aria-label="Tutup" class="text-[#8E8676] hover:text-[#2D2A24] w-8 h-8 rounded-full hover:bg-[#FAF8F4] flex items-center justify-center">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="overflow-y-auto px-6 py-5 space-y-5" id="modal-body"></div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div id="confirm-modal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-[fadeInScale_.2s_ease-out]">

        {{-- Header (gold gradient) --}}
        <div class="bg-gradient-to-br from-[#FFFBEA] via-[#FFF8E0] to-[#FEF3C7] px-6 pt-6 pb-5 text-center border-b border-[#FDE68A] relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-[#FCD34D]/40 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative mx-auto w-16 h-16 rounded-full bg-white shadow-md flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[#E5A100] fill" style="font-size: 36px;">how_to_vote</span>
            </div>
            <h3 class="relative font-bold text-[22px] text-[#2D2A24]">Konfirmasi Pilihan</h3>
            <p class="relative text-sm text-[#5C5648] mt-1">Pastikan kandidat di bawah ini adalah pilihan Anda</p>
        </div>

        {{-- Selected candidate preview --}}
        <div class="px-6 py-5">
            <div class="bg-[#FAF8F4] border border-[#E5E0D5] rounded-2xl p-4 flex items-center gap-4">
                <div id="confirm-photo" class="w-16 h-16 rounded-full bg-[#E5E0D5] overflow-hidden shrink-0 flex items-center justify-center ring-2 ring-[#E5A100]">
                    {{-- filled by JS --}}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-[#B45309] uppercase tracking-[0.12em] mb-0.5">Anda Memilih</p>
                    <p id="confirm-name" class="font-bold text-[17px] text-[#2D2A24] truncate leading-tight"></p>
                    <p id="confirm-number" class="text-xs text-[#8E8676] mt-0.5"></p>
                </div>
            </div>

            {{-- Warning --}}
            <div class="mt-4 bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                <span class="material-symbols-outlined text-red-600 text-[20px] mt-0.5 shrink-0">warning</span>
                <p class="text-xs text-red-800 leading-relaxed">
                    <strong>Pilihan tidak dapat diubah</strong> setelah dikirim. Suara Anda akan langsung tercatat dan terenkripsi.
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 pb-6 grid grid-cols-2 gap-3">
            <button type="button"
                    onclick="closeConfirm()"
                    class="h-12 rounded-full border-2 border-[#E5E0D5] text-[#5C5648] font-semibold text-sm hover:bg-[#FAF8F4] hover:border-[#8E8676] transition-colors">
                Batal
            </button>
            <button type="button"
                    id="confirm-submit-btn"
                    onclick="submitVote()"
                    class="h-12 rounded-full bg-[#E5A100] hover:bg-[#D97706] text-white font-bold text-sm shadow-lg shadow-[#E5A100]/30 transition-colors flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">send</span>
                <span>Ya, Kirim</span>
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(.92); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

@endsection

@push('scripts')
<script>
    const candidatesData = @json($candidatesJson);

    const cards = document.querySelectorAll('.candidate-card');
    const candidateInput = document.getElementById('candidate_id');
    const declaration = document.getElementById('declaration');
    const submitBtn = document.getElementById('submit-btn');
    const submitHint = document.getElementById('submit-hint');

    function updateSubmitState() {
        const ready = candidateInput.value && declaration.checked;
        submitBtn.disabled = !ready;
        if (ready) {
            submitHint.textContent = 'Klik tombol di atas untuk mengirim suara Anda. Pilihan tidak bisa diubah setelah dikirim.';
            submitHint.classList.remove('text-[#8E8676]');
            submitHint.classList.add('text-[#059669]', 'font-semibold');
        } else {
            submitHint.textContent = 'Pilih kandidat dan setujui pernyataan untuk mengirim suara.';
            submitHint.classList.remove('text-[#059669]', 'font-semibold');
            submitHint.classList.add('text-[#8E8676]');
        }
    }

    let firstSelectionDone = false;

    function selectCandidate(id) {
        const wasEmpty = !candidateInput.value;
        candidateInput.value = id;

        cards.forEach(card => {
            const isSelected = parseInt(card.dataset.candidateId) === id;
            card.setAttribute('aria-checked', isSelected ? 'true' : 'false');

            // Card: border, bg tint, ring halo, lift, shadow
            card.classList.toggle('border-[#E5A100]', isSelected);
            card.classList.toggle('border-[#E5E0D5]', !isSelected);
            card.classList.toggle('bg-[#FFFBEA]', isSelected);
            card.classList.toggle('bg-white', !isSelected);
            card.classList.toggle('ring-4', isSelected);
            card.classList.toggle('ring-[#E5A100]/25', isSelected);
            card.classList.toggle('shadow-[0_12px_40px_-8px_rgba(229,161,0,0.4)]', isSelected);
            card.classList.toggle('-translate-y-2', isSelected);

            // "PILIHAN ANDA" banner
            const banner = card.querySelector('.selected-banner');
            banner.classList.toggle('hidden', !isSelected);
            banner.classList.toggle('flex', isSelected);

            // Check indicator (top-right)
            const indicator = card.querySelector('.check-indicator');
            const icon = card.querySelector('.check-icon');
            indicator.classList.toggle('bg-[#E5A100]', isSelected);
            indicator.classList.toggle('border-[#E5A100]', isSelected);
            indicator.classList.toggle('scale-110', isSelected);
            indicator.classList.toggle('bg-white', !isSelected);
            indicator.classList.toggle('border-[#E5E0D5]', !isSelected);
            icon.classList.toggle('hidden', !isSelected);

            // Number badge
            const badge = card.querySelector('.number-badge');
            badge.classList.toggle('bg-[#E5A100]', isSelected);
            badge.classList.toggle('text-white', isSelected);
            badge.classList.toggle('shadow-md', isSelected);
            badge.classList.toggle('bg-[#F4EDE3]', !isSelected);
            badge.classList.toggle('text-[#5C5648]', !isSelected);
        });

        updateSubmitState();

        // On first selection, gently scroll to submit section so user sees next step
        if (wasEmpty && !firstSelectionDone) {
            firstSelectionDone = true;
            setTimeout(() => {
                document.getElementById('declaration').closest('section').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 350);
        }
    }

    declaration.addEventListener('change', updateSubmitState);

    // Modal
    function openDetail(id) {
        const c = candidatesData.find(x => x.id === id);
        if (!c) return;
        document.getElementById('modal-title').textContent = `Visi & Misi — ${c.name}`;
        const body = document.getElementById('modal-body');
        let html = '';
        if (c.visi) {
            html += `<div><h4 class="font-bold text-[15px] text-[#2D2A24] mb-2">Visi</h4><p class="text-sm text-[#5C5648] whitespace-pre-line leading-relaxed">${escapeHtml(c.visi)}</p></div>`;
        }
        if (c.misi) {
            html += `<div><h4 class="font-bold text-[15px] text-[#2D2A24] mb-2">Misi</h4><p class="text-sm text-[#5C5648] whitespace-pre-line leading-relaxed">${escapeHtml(c.misi)}</p></div>`;
        }
        if (c.video_url) {
            html += `<div class="pt-2 border-t border-[#E5E0D5]"><a href="${escapeHtml(c.video_url)}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-[#B45309] hover:text-[#7e5700]"><span class="material-symbols-outlined text-[18px]">play_circle</span>Lihat Video Kampanye</a></div>`;
        }
        if (!html) html = '<p class="text-sm text-[#8E8676]">Belum ada informasi visi & misi untuk kandidat ini.</p>';
        body.innerHTML = html;
        const modal = document.getElementById('detail-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeDetail() {
        const modal = document.getElementById('detail-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    // ====== Confirmation Modal ======
    const confirmModal = document.getElementById('confirm-modal');
    const confirmSubmitBtn = document.getElementById('confirm-submit-btn');

    function openConfirm() {
        const candidateId = parseInt(candidateInput.value);
        const c = candidatesData.find(x => x.id === candidateId);
        if (!c) return;

        // Populate photo from the matching card image
        const card = document.querySelector(`.candidate-card[data-candidate-id="${candidateId}"]`);
        const cardImg = card ? card.querySelector('img') : null;
        const photoEl = document.getElementById('confirm-photo');
        if (cardImg && cardImg.src) {
            photoEl.innerHTML = `<img src="${escapeHtml(cardImg.src)}" alt="${escapeHtml(c.name)}" class="w-full h-full object-cover">`;
        } else {
            photoEl.innerHTML = '<span class="material-symbols-outlined text-[#8E8676]" style="font-size: 32px;">person</span>';
        }

        document.getElementById('confirm-name').textContent = c.name;
        document.getElementById('confirm-number').textContent = `Nomor Urut ${String(c.sort_order).padStart(2, '0')}`;

        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirm() {
        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function submitVote() {
        // Disable both buttons + show loading state
        confirmSubmitBtn.disabled = true;
        confirmSubmitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span><span>Mengirim...</span>';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span><span>Mengirim...</span>';

        // Submit form natively (bypassing our submit listener)
        document.getElementById('vote-form').submit();
    }

    // Close confirm modal on backdrop click
    confirmModal.addEventListener('click', (e) => {
        if (e.target === confirmModal) closeConfirm();
    });

    // Close any open modal on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDetail();
            closeConfirm();
        }
    });

    // Intercept form submit → open modal instead of submitting directly
    document.getElementById('vote-form').addEventListener('submit', (e) => {
        e.preventDefault();
        if (!candidateInput.value || !declaration.checked) return;
        openConfirm();
    });

    // ===== Election Countdown Banner =====
    (function () {
        const banner = document.getElementById('election-countdown');
        if (!banner) return;
        const textEl = document.getElementById('election-countdown-text');
        const endDate = new Date(banner.dataset.end);

        const pad = n => String(n).padStart(2, '0');

        function setUrgency(level) {
            // Reset all urgency classes on banner
            banner.classList.remove(
                'bg-gradient-to-br', 'from-[#FFFBEA]', 'to-[#FEF3C7]', 'border-[#FCD34D]',
                'from-[#FEF3C7]', 'to-[#FDE68A]', 'border-[#F59E0B]',
                'from-red-50', 'to-red-100', 'border-red-400', 'animate-pulse'
            );
            textEl.classList.remove('text-[#7e5700]', 'text-[#B45309]', 'text-red-700');

            if (level === 'critical') {
                banner.classList.add('bg-gradient-to-br', 'from-red-50', 'to-red-100', 'border-red-400', 'animate-pulse');
                textEl.classList.add('text-red-700');
            } else if (level === 'warning') {
                banner.classList.add('bg-gradient-to-br', 'from-[#FEF3C7]', 'to-[#FDE68A]', 'border-[#F59E0B]');
                textEl.classList.add('text-[#B45309]');
            } else {
                banner.classList.add('bg-gradient-to-br', 'from-[#FFFBEA]', 'to-[#FEF3C7]', 'border-[#FCD34D]');
                textEl.classList.add('text-[#7e5700]');
            }
        }

        function tick() {
            const diff = endDate - new Date();
            if (diff <= 0) {
                textEl.textContent = 'Pemilihan telah berakhir';
                setUrgency('critical');
                clearInterval(interval);
                // Disable submit button — voting closed
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitHint.textContent = 'Waktu pemilihan telah berakhir.';
                    submitHint.classList.remove('text-[#059669]', 'font-semibold', 'text-[#8E8676]');
                    submitHint.classList.add('text-red-700', 'font-semibold');
                }
                return;
            }
            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const secs = Math.floor((diff % 60000) / 1000);

            if (days >= 1) {
                textEl.textContent = `${days} hari ${hours} jam ${pad(mins)} menit`;
                setUrgency('normal');
            } else if (hours >= 1) {
                textEl.textContent = `${pad(hours)}:${pad(mins)}:${pad(secs)}`;
                setUrgency('warning');
            } else {
                textEl.textContent = `${pad(mins)}:${pad(secs)}`;
                setUrgency('critical');
            }
        }

        tick();
        const interval = setInterval(tick, 1000);
    })();
</script>
@endpush
