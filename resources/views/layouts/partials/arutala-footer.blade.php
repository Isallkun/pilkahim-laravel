@php
    // Cari election yang hasilnya public (untuk link "Hasil Pemilihan" di footer).
    // Prioritas: active > completed; ambil yang paling baru.
    $publicResultElection = \App\Models\Election::where('result_visibility', 'public')
        ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
        ->latest('start_date')
        ->first();
@endphp

{{-- Site Footer — reusable 4-column footer --}}
<footer class="bg-[#FAF8F4] border-t border-[#E5E0D5]">
    <div class="max-w-[1280px] mx-auto px-8 pt-16 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
            {{-- Brand --}}
            <div class="lg:col-span-2 space-y-4">
                <a href="{{ route('home') }}" class="font-display text-[28px] text-[#E5A100]">Arutala</a>
                <p class="font-normal text-[14px] text-[#5C5648] max-w-[320px]">
                    Sistem Pemilihan Ketua Umum Arutala IAIC Pasuruan. Dirancang dengan prinsip transparansi dan kerahasiaan suara alumni.
                </p>
                <div class="flex gap-3 pt-2">
                    <a href="#" aria-label="Instagram" class="w-9 h-9 rounded-lg bg-white text-[#5C5648] flex items-center justify-center hover:bg-[#FEF9E7] transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="#" aria-label="YouTube" class="w-9 h-9 rounded-lg bg-white text-[#5C5648] flex items-center justify-center hover:bg-[#FEF9E7] transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                    <a href="#" aria-label="WhatsApp" class="w-9 h-9 rounded-lg bg-white text-[#5C5648] flex items-center justify-center hover:bg-[#FEF9E7] transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    </a>
                </div>
            </div>

            {{-- Navigasi --}}
            <div>
                <h4 class="font-bold text-[14px] uppercase tracking-wider text-[#2D2A24] mb-4">Navigasi</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('home') }}#home" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Beranda</a></li>
                    <li><a href="{{ route('home') }}#how-it-works" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Cara Memilih</a></li>
                    <li><a href="{{ route('home') }}#candidates" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Kandidat</a></li>
                    @if($publicResultElection)
                        <li>
                            <a href="{{ route('results.public', $publicResultElection) }}"
                               class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors inline-flex items-center gap-1.5">
                                Hasil Pemilihan
                                <span class="relative flex h-1.5 w-1.5" title="Live count tersedia">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                                </span>
                            </a>
                        </li>
                    @endif
                    <li><a href="#" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">FAQ</a></li>
                </ul>
            </div>

            {{-- Sistem --}}
            <div>
                <h4 class="font-bold text-[14px] uppercase tracking-wider text-[#2D2A24] mb-4">Sistem</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('login') }}" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Login Pemilih</a></li>
                    <li><a href="#" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Perhitungan Suara</a></li>
                    <li><a href="#" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Panduan Pengguna</a></li>
                    <li><a href="#" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">Aturan Pemilihan</a></li>
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <h4 class="font-bold text-[14px] uppercase tracking-wider text-[#2D2A24] mb-4">Kontak</h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[#E5A100] text-[18px] mt-0.5">location_on</span>
                        <span class="font-normal text-[14px] text-[#5C5648]">Jl. Raya Pasuruan No. 123, Jawa Timur</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[#E5A100] text-[18px] mt-0.5">mail</span>
                        <a href="mailto:kpu@hima-arutala.id" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">kpu@hima-arutala.id</a>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-[#E5A100] text-[18px] mt-0.5">chat</span>
                        <a href="tel:+6281234567890" class="font-normal text-[14px] text-[#5C5648] hover:text-[#D97706] transition-colors">+62 812 3456 7890</a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom Strip --}}
        <div class="mt-12 pt-8 border-t border-[#E5E0D5] flex flex-col md:flex-row items-center justify-between gap-3">
            <p class="font-medium text-[13px] text-[#8E8676]">&copy; {{ date('Y') }} Arutala — IAIC Pasuruan. Suara Kita, Masa Depan Bersama.</p>
            <p class="font-medium text-[13px] text-[#8E8676]">Made with ♡ Isallkun</p>
        </div>
    </div>
</footer>
