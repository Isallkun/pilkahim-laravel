{{-- Mobile Bottom Nav --}}
<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-2 py-3 bg-white shadow-[0_-4px_20px_0_rgba(229,161,0,0.1)] rounded-t-xl border-t border-[#F2EFE8]">
    <a href="{{ route('home') }}#home" class="m-nav-link flex flex-col items-center justify-center px-3 py-1">
        <span class="material-symbols-outlined">home</span>
        <span class="font-semibold text-[10px] mt-1">Beranda</span>
    </a>
    <a href="{{ route('home') }}#how-it-works" class="m-nav-link flex flex-col items-center justify-center px-3 py-1">
        <span class="material-symbols-outlined">checklist</span>
        <span class="font-medium text-[10px] mt-1">Cara Kerja</span>
    </a>
    <a href="{{ route('home') }}#candidates" class="m-nav-link flex flex-col items-center justify-center px-3 py-1">
        <span class="material-symbols-outlined">groups</span>
        <span class="font-medium text-[10px] mt-1">Kandidat</span>
    </a>
    <a href="{{ route('home') }}#keamanan" class="m-nav-link flex flex-col items-center justify-center px-3 py-1">
        <span class="material-symbols-outlined">shield</span>
        <span class="font-medium text-[10px] mt-1">Keamanan</span>
    </a>
    @auth
        <form method="POST" action="{{ route('logout') }}" class="contents">
            @csrf
            <button type="submit" class="flex flex-col items-center justify-center bg-[#E5A100] text-white rounded-full px-4 py-2">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-semibold text-[10px] mt-1">Keluar</span>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center bg-[#E5A100] text-white rounded-full px-4 py-2">
            <span class="material-symbols-outlined">login</span>
            <span class="font-semibold text-[10px] mt-1">Masuk</span>
        </a>
    @endauth
</nav>
<div class="md:hidden h-20"></div>
