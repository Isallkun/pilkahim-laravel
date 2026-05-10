@extends('layouts.arutala')

@section('title', 'Suara Telah Tercatat')

@section('body-class', 'antialiased text-[#2D2A24] bg-[#FFFCF5] min-h-screen flex flex-col')

@push('head')
<style>
    .success-glow {
        box-shadow: 0 0 60px rgba(229, 161, 0, 0.25);
    }
    @keyframes sparkle {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.85); }
    }
    .sparkle-anim { animation: sparkle 2s ease-in-out infinite; }
    .sparkle-anim-delay { animation: sparkle 2.4s ease-in-out infinite .8s; }
</style>
@endpush

@section('body')

{{-- Header (logo + logout) --}}
<header class="bg-[#FFFCF5]/85 backdrop-blur-md sticky top-0 w-full z-40 border-b border-[#F2EFE8]">
    <div class="flex justify-between items-center w-full px-6 sm:px-8 max-w-[1280px] mx-auto h-[72px]">
        <a href="{{ route('home') }}" class="font-display text-h1 text-[#E5A100]">Arutala</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 bg-white border border-[#E5E0D5] text-[#5C5648] hover:text-[#E5A100] hover:border-[#E5A100] font-medium text-sm px-4 py-2 rounded-full transition-colors">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </form>
    </div>
</header>

<main class="flex-grow px-6 sm:px-8 py-10 md:py-12 max-w-[800px] mx-auto w-full flex flex-col items-center">

    {{-- Hero --}}
    <div class="text-center mb-10 relative w-full pt-4">
        <div class="w-[120px] h-[120px] mx-auto mb-6 bg-[#FFFBEA] rounded-full flex items-center justify-center success-glow relative">
            <span class="material-symbols-outlined fill text-[#E5A100]" style="font-size: 80px;">check_circle</span>
            {{-- Sparkles --}}
            <span class="material-symbols-outlined fill absolute -top-1 -right-1 text-[#FCD34D] sparkle-anim" style="font-size: 28px;">stars</span>
            <span class="material-symbols-outlined fill absolute bottom-3 -left-1 text-[#FDE68A] sparkle-anim-delay" style="font-size: 22px;">stars</span>
            <span class="material-symbols-outlined fill absolute top-6 -left-2 text-[#FBBF24] sparkle-anim-delay" style="font-size: 16px;">stars</span>
        </div>

        <h1 class="font-display text-[28px] sm:text-[36px] font-bold text-[#7e5700] inline-block relative leading-tight">
            Suara Anda Sudah Tercatat
            <span class="absolute -bottom-1 left-0 w-full h-[3px] bg-[#E5A100] rounded-full opacity-50"></span>
        </h1>

        <p class="text-base sm:text-[17px] text-[#5C5648] mt-6 max-w-lg mx-auto leading-relaxed">
            Terima kasih, <strong class="text-[#2D2A24]">{{ $user->name }}</strong>, atas partisipasi Anda dalam pemilihan ini. Suara Anda akan tetap <strong class="text-[#2D2A24]">rahasia</strong>.
        </p>
    </div>

    {{-- Receipt Card --}}
    <div class="w-full bg-white rounded-2xl border border-[#E5E0D5] shadow-lg shadow-[#E5A100]/5 mb-8 relative overflow-hidden p-1">
        <div class="border-2 border-dashed border-[#E5E0D5] rounded-xl p-6 sm:p-8 flex flex-col items-center">

            <div class="bg-[#FCD34D]/40 text-[#7e5700] font-bold text-[11px] tracking-[0.18em] px-4 py-2 rounded-full mb-6 inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined fill text-[14px]">workspace_premium</span>
                BUKTI PARTISIPASI RESMI
            </div>

            <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8 text-left">
                <div>
                    <p class="text-xs text-[#8E8676] mb-1 uppercase tracking-wider">Nama</p>
                    <p class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24] leading-tight">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#8E8676] mb-1 uppercase tracking-wider">NIM</p>
                    <p class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24] leading-tight">{{ $user->username }}</p>
                </div>

                @if($user->angkatan)
                    <div>
                        <p class="text-xs text-[#8E8676] mb-1 uppercase tracking-wider">Angkatan</p>
                        <p class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24] leading-tight">{{ $user->angkatan }}</p>
                    </div>
                @endif

                <div class="@if(!$user->angkatan) sm:col-span-2 @endif">
                    <p class="text-xs text-[#8E8676] mb-1 uppercase tracking-wider">Pemilihan</p>
                    <p class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24] leading-tight">{{ $election->name }}</p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-xs text-[#8E8676] mb-1 uppercase tracking-wider">Waktu Pencatatan</p>
                    <p class="font-bold text-[18px] sm:text-[20px] text-[#2D2A24] leading-tight">{{ $votedAt }}</p>
                </div>
            </div>

            <hr class="w-full border-t border-[#FCD34D] my-6">

            <p class="text-xs italic text-[#8E8676] text-center inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">cloud_done</span>
                Bukti partisipasi tersimpan otomatis di sistem
            </p>
        </div>
    </div>

    {{-- Reassurance Strip --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full mb-8">
        <div class="bg-white border border-[#E5E0D5] rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#FFFBEA] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined fill text-[#E5A100] text-[20px]">verified_user</span>
            </div>
            <p class="text-sm font-medium text-[#2D2A24]">Identitas Terverifikasi</p>
        </div>
        <div class="bg-white border border-[#E5E0D5] rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#FFFBEA] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined fill text-[#E5A100] text-[20px]">task</span>
            </div>
            <p class="text-sm font-medium text-[#2D2A24]">Tercatat Permanen</p>
        </div>
        <div class="bg-white border border-[#E5E0D5] rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#FFFBEA] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[#E5A100] text-[20px]">visibility_off</span>
            </div>
            <p class="text-sm font-medium text-[#2D2A24]">Pilihan Dirahasiakan</p>
        </div>
    </div>

    {{-- Action + Countdown --}}
    <div class="flex flex-col items-center gap-3 w-full mt-6 mb-10">
        <form id="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="bg-[#E5A100] hover:bg-[#D97706] text-white font-bold text-[15px] rounded-full px-8 py-3.5 transition-colors flex items-center justify-center gap-2 shadow-lg shadow-[#E5A100]/30">
                <span class="material-symbols-outlined">logout</span>
                Selesai &amp; Keluar
            </button>
        </form>

        {{-- Countdown pill — pengingat auto-logout --}}
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-[#FFFBEA] border border-[#FDE68A] rounded-full shadow-sm">
            <span class="material-symbols-outlined fill text-[#E5A100] text-[18px]">timer</span>
            <span class="text-sm text-[#5C5648]">
                Otomatis kembali ke beranda dalam
                <span id="countdown-num" class="font-bold text-[#E5A100] tabular-nums">15</span>
                detik
            </span>
        </div>
    </div>

    {{-- Quote --}}
    <div class="text-center flex flex-col items-center gap-3 opacity-90 mb-8">
        <div class="w-16 h-px bg-[#8E8676]/40"></div>
        <p class="text-base sm:text-lg italic text-[#7e5700] font-semibold max-w-md leading-relaxed">
            "Suara Anda adalah amanah. Pilihan Anda terjaga rahasianya."
        </p>
        <div class="w-16 h-px bg-[#8E8676]/40"></div>
    </div>
</main>

{{-- Minimal Footer --}}
<footer class="w-full py-6 px-6 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 max-w-[1200px] mx-auto text-center sm:text-left border-t border-[#E5E0D5]">
    <p class="text-xs text-[#8E8676]">
        &copy; {{ date('Y') }} Arutala — IAIC Pasuruan.
    </p>
    <div class="flex gap-5 text-xs">
        <a href="{{ route('home') }}" class="text-[#8E8676] hover:text-[#E5A100] transition-colors">Beranda</a>
        <a href="#" class="text-[#8E8676] hover:text-[#E5A100] transition-colors">Bantuan</a>
    </div>
</footer>

@endsection

@push('scripts')
<script>
    // Auto-logout countdown
    let countdown = 15;
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
