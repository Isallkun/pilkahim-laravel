{{-- Top App Bar — reusable top navigation --}}
<header class="bg-white/95 backdrop-blur-md sticky top-0 w-full z-50 border-b border-[#F2EFE8]">
    <div class="flex justify-between items-center w-full px-8 max-w-[1280px] mx-auto h-[72px]">
        <a href="{{ route('home') }}" class="font-display text-h1 text-[#E5A100]">Arutala</a>

        <nav class="hidden md:flex gap-8 items-center">
            <a class="nav-link" href="{{ route('home') }}#home">Beranda</a>
            <a class="nav-link" href="{{ route('home') }}#how-it-works">Cara Kerja</a>
            <a class="nav-link" href="{{ route('home') }}#candidates">Kandidat</a>
            <a class="nav-link" href="{{ route('home') }}#keamanan">Keamanan</a>
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-[#E5A100] hover:bg-[#D97706] text-white font-semibold text-sm px-5 py-2.5 rounded-full transition-colors shadow-md shadow-[#E5A100]/20">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-[#E5A100] hover:bg-[#D97706] text-white font-semibold text-sm px-5 py-2.5 rounded-full transition-colors shadow-md shadow-[#E5A100]/20">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    <span>Masuk</span>
                </a>
            @endauth
        </div>
    </div>
</header>
