@extends('layouts.arutala')

@section('title', 'Suara Tercatat')

@section('body-class', 'antialiased text-[#2D2A24] bg-gradient-to-br from-[#FFFBEA] via-[#FFF8E0] to-white min-h-screen')

@section('body')
<div class="min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Decorative blobs --}}
    <div class="absolute top-0 right-0 -mr-40 -mt-40 w-[500px] h-[500px] bg-[#FCD34D]/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 -ml-40 -mb-40 w-[400px] h-[400px] bg-[#E5A100]/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative w-full max-w-md">

        {{-- Logo top --}}
        <div class="text-center mb-6">
            <span class="font-display text-[28px] text-[#E5A100]">Arutala</span>
        </div>

        {{-- Success Card --}}
        <div class="bg-white rounded-3xl border border-[#E5E0D5] shadow-2xl shadow-[#E5A100]/10 p-8 text-center relative overflow-hidden">

            {{-- Internal blob --}}
            <div class="absolute -top-16 -right-16 w-32 h-32 bg-[#FCD34D]/20 rounded-full blur-2xl pointer-events-none"></div>

            {{-- Success Icon --}}
            <div class="relative mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-[#E5A100] to-[#D97706] flex items-center justify-center shadow-lg shadow-[#E5A100]/40">
                <span class="material-symbols-outlined text-white fill" style="font-size: 44px;">check_circle</span>
            </div>

            {{-- Title --}}
            <h1 class="relative mt-6 font-bold text-[26px] sm:text-[28px] text-[#2D2A24] leading-tight">
                Suara Anda <span class="text-[#E5A100]">Tercatat!</span>
            </h1>
            <p class="relative mt-2 text-[#5C5648] text-sm">
                Terima kasih telah berpartisipasi dalam<br>
                <span class="font-semibold text-[#2D2A24]">{{ $election->name }}</span>
            </p>

            {{-- Token Card --}}
            <div class="relative mt-6 rounded-2xl border-2 border-dashed border-[#E5A100] bg-[#FFFBEA] p-5">
                <p class="text-[10px] font-bold text-[#B45309] uppercase tracking-[0.15em] mb-2">Token Bukti Voting</p>
                <p id="evidence-token" class="font-mono text-[20px] sm:text-[24px] font-bold tracking-wider text-[#2D2A24] break-all leading-tight">{{ $evidenceToken }}</p>
                <button type="button"
                        id="copy-btn"
                        onclick="copyToken()"
                        class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[#B45309] hover:text-[#7e5700] transition-colors">
                    <span class="material-symbols-outlined text-[16px]" id="copy-icon">content_copy</span>
                    <span id="copy-text">Salin Token</span>
                </button>
            </div>

            <p class="relative mt-4 text-xs text-[#8E8676] leading-relaxed">
                Simpan token ini sebagai bukti partisipasi. Suara Anda <strong>terenkripsi & anonim</strong> — tidak ada yang tahu pilihan Anda.
            </p>

            {{-- Countdown --}}
            <div class="relative mt-6 rounded-xl bg-[#FAF8F4] border border-[#E5E0D5] p-4">
                <p class="text-sm text-[#5C5648]">
                    Anda akan otomatis logout dalam
                    <span class="font-bold text-[#E5A100]" id="countdown-num">10</span>
                    detik
                </p>
            </div>

            {{-- Manual logout --}}
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="relative mt-4">
                @csrf
                <button type="submit" class="text-sm font-semibold text-[#5C5648] hover:text-[#E5A100] inline-flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    Logout sekarang
                </button>
            </form>
        </div>

        {{-- Footer microcopy --}}
        <p class="text-center text-xs text-[#8E8676] mt-6">
            &copy; {{ date('Y') }} HIMA Arutala IAIC Pasuruan
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Copy token to clipboard
    function copyToken() {
        const token = document.getElementById('evidence-token').textContent.trim();
        const text = document.getElementById('copy-text');
        const icon = document.getElementById('copy-icon');
        const fallback = () => {
            const ta = document.createElement('textarea');
            ta.value = token;
            ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(ta);
        };
        const showSuccess = () => {
            text.textContent = 'Token tersalin';
            icon.textContent = 'check';
            setTimeout(() => { text.textContent = 'Salin Token'; icon.textContent = 'content_copy'; }, 2000);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(token).then(showSuccess).catch(() => { fallback(); showSuccess(); });
        } else {
            fallback();
            showSuccess();
        }
    }

    // Auto-logout countdown
    let countdown = 10;
    const cdEl = document.getElementById('countdown-num');
    const interval = setInterval(() => {
        countdown--;
        if (cdEl) cdEl.textContent = countdown;
        if (countdown <= 0) {
            clearInterval(interval);
            document.getElementById('logout-form').submit();
        }
    }, 1000);
</script>
@endpush
