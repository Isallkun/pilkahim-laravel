@extends('layouts.arutala')

@section('title', 'Masuk')

@section('body-class', 'bg-white text-[#2D2A24] antialiased')

@section('body')
<div class="min-h-screen lg:grid lg:grid-cols-2">

    {{-- LEFT: Brand visual (desktop only) --}}
    <aside class="hidden lg:flex relative bg-gradient-to-br from-[#FFFBEA] via-[#FFF8E0] to-[#FEF3C7] p-12 pl-16 flex-col justify-between overflow-hidden">
        {{-- decorative blobs --}}
        <div class="absolute top-0 right-0 -mr-32 -mt-32 w-[500px] h-[500px] bg-[#FCD34D]/40 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-32 -mb-32 w-[400px] h-[400px] bg-[#E5A100]/20 rounded-full blur-3xl"></div>

        {{-- Top: Logo + back link --}}
        <div class="relative z-10 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-display text-h1 text-[#E5A100]">Arutala</a>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-[#5C5648] hover:text-[#E5A100] transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali ke Beranda
            </a>
        </div>

        {{-- Middle: Headline + visual --}}
        <div class="relative z-10 space-y-8">
            <div class="space-y-4">
                <h1 class="font-display text-[48px] leading-[1.1] font-extrabold text-[#2D2A24]">
                    <span class="text-[#E5A100]">Pilihanmu,</span><br>
                    Masa Depan <span class="text-[#E5A100]">Arutala</span>
                </h1>
                <p class="text-[#5C5648] text-lg max-w-md">
                    Masuk dengan akun pemilih untuk memberikan suaramu pada pemilihan ketua umum Arutala IAIC Pasuruan.
                </p>
            </div>

            {{-- Floating verification badge --}}
            <div class="bg-white p-4 rounded-2xl shadow-lg border border-[#E5E0D5] flex items-center gap-3 w-fit">
                <div class="bg-[#FEF9E7] text-[#E5A100] p-2.5 rounded-full">
                    <span class="material-symbols-outlined fill">verified_user</span>
                </div>
                <div>
                    <p class="font-bold text-sm text-[#2D2A24]">Voting Aman</p>
                    <p class="text-xs text-[#5C5648]">Terenkripsi & terverifikasi sistem</p>
                </div>
            </div>

            {{-- Trust points --}}
            <ul class="space-y-2 pt-2">
                <li class="flex items-center gap-2 text-sm text-[#5C5648]">
                    <span class="material-symbols-outlined text-[#E5A100] text-[18px]">check_circle</span>
                    Suara terenkripsi dan anonim
                </li>
                <li class="flex items-center gap-2 text-sm text-[#5C5648]">
                    <span class="material-symbols-outlined text-[#E5A100] text-[18px]">check_circle</span>
                    Bukti partisipasi unik untuk setiap pemilih
                </li>
                <li class="flex items-center gap-2 text-sm text-[#5C5648]">
                    <span class="material-symbols-outlined text-[#E5A100] text-[18px]">check_circle</span>
                    Hasil real-time setelah voting selesai
                </li>
            </ul>
        </div>

        {{-- Bottom: Copyright --}}
        <p class="relative z-10 text-xs text-[#8E8676]">
            &copy; {{ date('Y') }} Arutala — IAIC Pasuruan
        </p>
    </aside>

    {{-- RIGHT: Login form --}}
    <main class="flex flex-col items-center justify-center px-6 py-12 sm:px-12 bg-white min-h-screen lg:min-h-0">
        <div class="w-full max-w-sm">
            {{-- Mobile logo + back --}}
            <div class="lg:hidden flex items-center justify-between mb-8">
                <a href="{{ route('home') }}" class="font-display text-[28px] text-[#E5A100]">Arutala</a>
                <a href="{{ route('home') }}" class="text-sm text-[#5C5648] hover:text-[#E5A100] inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Beranda
                </a>
            </div>

            {{-- Heading --}}
            <div class="mb-8">
                <h2 class="font-bold text-[28px] text-[#2D2A24] leading-tight">Selamat Datang</h2>
                <p class="text-[#5C5648] text-sm mt-2">Masuk dengan kredensial pemilih untuk melanjutkan.</p>
            </div>

            {{-- Success message --}}
            @if (session('success'))
                <div class="mb-5 rounded-xl bg-green-50 border border-green-200 p-3 flex items-start gap-2">
                    <span class="material-symbols-outlined text-green-600 text-[20px] mt-0.5">check_circle</span>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Error messages --}}
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-3 flex items-start gap-2">
                    <span class="material-symbols-outlined text-red-600 text-[20px] mt-0.5">error</span>
                    <p class="text-sm text-red-800">{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-semibold text-[#2D2A24] mb-2">Username</label>
                    <div class="flex items-center border border-[#E5E0D5] rounded-xl focus-within:border-[#E5A100] focus-within:ring-4 focus-within:ring-[#E5A100]/10 transition-all bg-white">
                        <span class="material-symbols-outlined text-[#8E8676] text-[20px] pl-4 pr-3 shrink-0">person</span>
                        <input id="username"
                               name="username"
                               type="text"
                               required
                               autocomplete="username"
                               autofocus
                               value="{{ old('username') }}"
                               placeholder="Contoh: 001"
                               class="flex-1 py-3.5 pr-4 bg-transparent border-none outline-none text-[15px] text-[#2D2A24] placeholder-[#8E8676]">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-[#2D2A24] mb-2">Password</label>
                    <div class="flex items-center border border-[#E5E0D5] rounded-xl focus-within:border-[#E5A100] focus-within:ring-4 focus-within:ring-[#E5A100]/10 transition-all bg-white">
                        <span class="material-symbols-outlined text-[#8E8676] text-[20px] pl-4 pr-3 shrink-0">lock</span>
                        <input id="password"
                               name="password"
                               type="password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan password"
                               class="flex-1 py-3.5 bg-transparent border-none outline-none text-[15px] text-[#2D2A24] placeholder-[#8E8676]">
                        <button type="button"
                                onclick="togglePassword()"
                                aria-label="Tampilkan password"
                                class="pr-4 pl-2 flex items-center text-[#8E8676] hover:text-[#E5A100] transition-colors shrink-0">
                            <span class="material-symbols-outlined text-[20px] leading-none" id="password-toggle-icon">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full text-base py-3.5">
                    <span>Masuk</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </form>

            {{-- Helper note --}}
            <div class="mt-6 p-4 bg-[#FFFBEA] border border-[#FCD34D]/40 rounded-xl">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[#B45309] text-[18px] mt-0.5">info</span>
                    <div>
                        <p class="text-xs font-semibold text-[#B45309]">Belum punya akun?</p>
                        <p class="text-xs text-[#5C5648] mt-0.5">Akun pemilih dibuat otomatis dari Daftar Pemilih Tetap (DPT). Hubungi panitia jika belum menerima kredensial.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('password-toggle-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
</script>
@endpush
