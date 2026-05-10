@extends('layouts.arutala')

@section('title', 'E-Voting')

@section('body')
    @include('layouts.partials.arutala-header')

    <main class="max-w-[1200px] mx-auto px-gutter pb-xl relative">
        {{-- Background Blobs --}}
        <div class="absolute top-0 right-0 -mr-[20%] -mt-[10%] w-[800px] h-[800px] bg-secondary-fixed/30 rounded-full blur-3xl -z-10 opacity-60 pointer-events-none"></div>
        <div class="absolute bottom-[20%] left-0 -ml-[20%] w-[600px] h-[600px] bg-primary-fixed/20 rounded-full blur-3xl -z-10 opacity-50 pointer-events-none"></div>

        {{-- Hero Section --}}
        <section id="home" class="py-12 md:py-[100px] flex flex-col md:flex-row items-center gap-xl">
            <div class="flex-1 space-y-lg relative z-10">

                @if($election && $election->show_countdown && $election->end_date && $election->end_date->isFuture())
                    <div id="election-countdown"
                         data-end="{{ $election->end_date->toIso8601String() }}"
                         class="inline-flex items-center gap-2 px-4 py-2 bg-[#FFFBEA] border border-[#FCD34D] rounded-full shadow-sm">
                        <span class="material-symbols-outlined fill text-[#E5A100] text-[18px]">timer</span>
                        <span class="text-sm font-semibold text-[#7e5700]">
                            <span class="text-[#8E8676] font-medium">Pemilihan ditutup dalam</span>
                            <span id="election-countdown-text" class="ml-1 tabular-nums">memuat…</span>
                        </span>
                    </div>
                @endif

                <h1 class="font-display text-display text-[#2D2A24] relative">
                    <span class="text-[#E5A100]">Pilihanmu,</span><br>Masa Depan <span class="text-[#E5A100]">Arutala IAICPAS</span>
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
                    Sistem e-voting transparan untuk pemilihan ketua umum Arutala. Berikan suaramu dengan mudah, aman, dan pantau hasilnya secara real-time. Optimisme melalui transparansi.
                </p>
                <div class="pt-sm flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="bg-primary-container hover:bg-primary text-on-primary font-label-caps text-label-caps h-[56px] px-xl rounded-full transition-all inline-flex items-center justify-center gap-sm glow-shadow">
                        <span>Mulai Memilih</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>

                    @if($election && $election->isResultPublic())
                        <a href="{{ route('results.public', $election) }}"
                           class="bg-white border-2 border-[#E5A100] text-[#B45309] hover:bg-[#FFFBEA] font-label-caps text-label-caps h-[56px] px-xl rounded-full transition-all inline-flex items-center justify-center gap-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                            <span>Lihat Hasil Live</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="flex-1 relative w-full aspect-square md:aspect-auto md:h-[600px]">
                <div class="w-full h-full bg-surface-container-highest rounded-[40px] overflow-hidden border border-outline-variant/30 relative">
                    <img alt="Student voting" class="w-full h-full object-cover mix-blend-multiply opacity-90" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBxfIChd1x95BNF_7lao-I-cV3szCwPgOjXDW5s-jWPrWeABUT8wICXktRfJi9kpciKWgojTbf-3BzMOUd9V1nL10j1-lOheapTHYIJYfPpklokwTtSHSR38jSG3dg7VYaAuCGOYr8De6iQyoDThNWa7gHl2o7g3OJj65vJYf9mZQFkb880NXZ0G8VevDaaRmIqMjNA0qAFWj0w7ctJkIsSmN7IPoFEpt672IrYWx8_PLvPIfvZmItSFkH8zTD2Xh5PKmT_MzGMIY0">
                </div>
                <div class="absolute -bottom-6 -left-6 bg-surface p-md rounded-2xl shadow-lg border border-outline-variant flex items-center gap-3">
                    <div class="bg-secondary-container text-on-secondary-container w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined fill text-[20px] leading-none">verified_user</span>
                    </div>
                    <div>
                        <p class="font-label-caps text-label-caps text-on-surface leading-tight">Voting Aman</p>
                        <p class="text-xs text-on-surface-variant leading-tight">Terverifikasi sistem</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- How It Works Section --}}
        <section id="how-it-works" class="py-12 md:py-[100px]">
            <div class="text-center mb-xl relative">
                <h2 class="font-h1 text-h1 text-on-surface">Cara Kerja</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-sm">Tiga langkah mudah untuk menentukan masa depan kampusmu.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
                <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-[24px] card-hover transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-surface-container-low rounded-bl-[100px] -z-10 group-hover:bg-secondary-fixed/20 transition-colors"></div>
                    <div class="w-14 h-14 bg-surface-container flex items-center justify-center rounded-xl mb-md text-primary-container">
                        <span class="material-symbols-outlined">login</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-on-surface mb-xs">1. Login</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Masuk menggunakan kredensial mahasiswa aktifmu untuk verifikasi identitas yang aman.</p>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-[24px] card-hover transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-surface-container-low rounded-bl-[100px] -z-10 group-hover:bg-secondary-fixed/20 transition-colors"></div>
                    <div class="w-14 h-14 bg-surface-container flex items-center justify-center rounded-xl mb-md text-primary-container">
                        <span class="material-symbols-outlined">how_to_vote</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-on-surface mb-xs">2. Pilih Kandidat</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Pelajari visi dan misi setiap kandidat, lalu tentukan pilihan terbaikmu dengan satu ketukan.</p>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant p-lg rounded-[24px] card-hover transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-surface-container-low rounded-bl-[100px] -z-10 group-hover:bg-secondary-fixed/20 transition-colors"></div>
                    <div class="w-14 h-14 bg-surface-container flex items-center justify-center rounded-xl mb-md text-primary-container">
                        <span class="material-symbols-outlined">query_stats</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-on-surface mb-xs">3. Pantau Hasil</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Lihat perolehan suara secara transparan dan real-time setelah periode pemilihan berakhir.</p>
                </div>
            </div>
        </section>
    </main>

    {{-- SECTION 3: Kandidat Preview (Dynamic) --}}
    <section id="candidates" class="bg-[#FFFFFF] py-[100px]">
        <div class="max-w-[1280px] mx-auto px-gutter">
            <div class="text-center mb-12">
                <div class="bg-[#FEF9E7] text-[#B45309] rounded-full px-4 py-1.5 font-medium text-[13px] mx-auto w-fit mb-4">👥 Kandidat Pemilihan</div>
                <h2 class="font-bold text-[40px] text-[#2D2A24] mb-4">Kenali <span class="text-[#E5A100]">Calon Pemimpin</span> Arutala</h2>
            </div>

            @if($candidates->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[24px]">
                    @foreach($candidates as $candidate)
                        <div class="bg-white border border-[#E5E0D5] rounded-3xl overflow-hidden hover:border-[#FCD34D] hover:-translate-y-1 hover:shadow-[0_8px_30px_-8px_rgba(229,161,0,0.25)] transition-all duration-300 flex flex-col">
                            {{-- Photo + Number Badge --}}
                            <div class="aspect-[4/5] bg-[#F4EDE3] relative">
                                @if($candidate->photo_path)
                                    <img src="{{ Storage::url($candidate->photo_path) }}" alt="Foto {{ $candidate->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#8E8676]">
                                        <span class="material-symbols-outlined" style="font-size: 80px;">person</span>
                                    </div>
                                @endif
                                <div class="absolute top-4 left-4 w-[56px] h-[56px] bg-[#E5A100] rounded-full flex items-center justify-center text-white font-extrabold text-[24px] shadow-lg">
                                    {{ str_pad($candidate->sort_order, 2, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-6 flex flex-col flex-1">
                                <h3 class="font-bold text-[20px] text-[#2D2A24] leading-tight">{{ $candidate->name }}</h3>
                                <p class="text-xs text-[#8E8676] font-medium mt-1">Nomor Urut {{ $candidate->sort_order }}</p>

                                {{-- Visi tagline --}}
                                @if($candidate->visi)
                                    <div class="bg-[#FFFBEA] border border-[#FDE68A]/60 px-4 py-3 rounded-xl mt-4">
                                        <p class="italic text-sm text-[#B45309] leading-snug">"{{ Str::limit($candidate->visi, 100) }}"</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @php
                    $placeholderCandidates = [
                        [
                            'number' => '01',
                            'name' => 'Ahmad Rizki Pratama',
                            'subtitle' => 'NIM 2022010101 · Sistem Informasi',
                            'tagline' => 'Arutala Maju Bersama',
                            'visi' => 'Membangun HIMA yang adaptif dan kolaboratif melalui program kerja berbasis data, mengedepankan transparansi anggaran, serta memperluas kemitraan lintas organisasi mahasiswa.',
                        ],
                        [
                            'number' => '02',
                            'name' => 'Siti Nurhaliza',
                            'subtitle' => 'NIM 2022010202 · Manajemen',
                            'tagline' => 'Inovasi untuk Mahasiswa',
                            'visi' => 'Mengakselerasi kreativitas mahasiswa lewat inkubator ide, pelatihan kepemimpinan rutin, dan ekosistem kolaborasi yang inklusif untuk seluruh angkatan dan jurusan.',
                        ],
                        [
                            'number' => '03',
                            'name' => 'Muhammad Faiz',
                            'subtitle' => 'NIM 2022010303 · Pendidikan Bahasa Arab',
                            'tagline' => 'Solid, Solidaritas, Solutif',
                            'visi' => 'Memperkuat soliditas internal HIMA, menghidupkan budaya gotong royong antar departemen, serta menghadirkan solusi konkret bagi keluhan dan kebutuhan mahasiswa sehari-hari.',
                        ],
                    ];
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[24px]">
                    @foreach($placeholderCandidates as $candidate)
                        <div class="bg-[#FFFFFF] border border-[#E5E0D5] rounded-3xl overflow-hidden hover:border-[#FCD34D] hover:-translate-y-1 transition-all duration-300">
                            <div class="aspect-[4/5] bg-[#E5E0D5] relative">
                                <div class="absolute top-4 left-4 w-[56px] h-[56px] bg-[#E5A100] rounded-full flex items-center justify-center text-white font-extrabold text-[24px]">
                                    {{ $candidate['number'] }}
                                </div>
                            </div>
                            <div class="p-[24px]">
                                <h3 class="font-bold text-[20px] text-[#2D2A24]">{{ $candidate['name'] }}</h3>
                                <p class="font-medium text-[14px] text-[#8E8676] mt-1">{{ $candidate['subtitle'] }}</p>
                                <div class="italic font-medium bg-[#FFFBEA] rounded-lg text-[#B45309] px-3 py-2 text-sm mt-3">"{{ $candidate['tagline'] }}"</div>
                                <p class="text-sm text-[#5C5648] line-clamp-3 mt-4 mb-6">{{ $candidate['visi'] }}</p>
                                <a href="{{ route('login') }}" class="block w-full bg-white border-2 border-[#E5A100] text-[#B45309] py-2 rounded-xl font-semibold hover:bg-[#FFFBEA] transition-colors text-center">Lihat Profil Lengkap →</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- SECTION 4: Fitur Keamanan --}}
    <section id="keamanan" class="bg-[#FFFBEA] py-[100px]">
        <div class="max-w-[1280px] mx-auto px-gutter">
            <div class="text-center mb-12">
                <div class="bg-[#FFFFFF] border border-[#FCD34D] text-[#B45309] rounded-full px-4 py-1.5 font-medium text-[13px] mx-auto w-fit mb-4">🛡 Keamanan & Transparansi</div>
                <h2 class="font-bold text-[40px] text-[#2D2A24] mb-4">Suaramu, <span class="text-[#E5A100] underline decoration-[#FBBF24] decoration-wavy underline-offset-8">Rahasiamu</span></h2>
                <p class="font-normal text-[17px] text-[#5C5648] max-w-[600px] mx-auto">Sistem dirancang dengan prinsip transparan dalam proses, tapi rahasia dalam pilihan untuk memastikan demokrasi yang sehat di lingkungan kampus.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-[20px]">
                <div class="bg-[#FFFFFF] rounded-2xl p-[28px] shadow-[0_4px_20px_rgba(229,161,0,0.05)]">
                    <div class="w-[52px] h-[52px] bg-[#FEF9E7] rounded-xl flex items-center justify-center text-[#D97706] mb-4">
                        <span class="material-symbols-outlined">lock</span>
                    </div>
                    <h3 class="font-semibold text-[18px] text-[#2D2A24] mb-2">Suara Terenkripsi</h3>
                    <p class="font-normal text-[14px] text-[#5C5648]">Setiap suara yang masuk dienkripsi dengan standar keamanan tinggi untuk menjaga kerahasiaan pilihan pemilih.</p>
                </div>
                <div class="bg-[#FFFFFF] rounded-2xl p-[28px] shadow-[0_4px_20px_rgba(229,161,0,0.05)]">
                    <div class="w-[52px] h-[52px] bg-[#FEF9E7] rounded-xl flex items-center justify-center text-[#D97706] mb-4">
                        <span class="material-symbols-outlined">receipt_long</span>
                    </div>
                    <h3 class="font-semibold text-[18px] text-[#2D2A24] mb-2">Bukti Voting Unik</h3>
                    <p class="font-normal text-[14px] text-[#5C5648]">Dapatkan kode unik sebagai bukti partisipasi tanpa mengungkap kandidat mana yang telah dipilih.</p>
                </div>
                <div class="bg-[#FFFFFF] rounded-2xl p-[28px] shadow-[0_4px_20px_rgba(229,161,0,0.05)]">
                    <div class="w-[52px] h-[52px] bg-[#FEF9E7] rounded-xl flex items-center justify-center text-[#D97706] mb-4">
                        <span class="material-symbols-outlined">history</span>
                    </div>
                    <h3 class="font-semibold text-[18px] text-[#2D2A24] mb-2">Audit Trail Lengkap</h3>
                    <p class="font-normal text-[14px] text-[#5C5648]">Seluruh log aktivitas sistem dicatat secara detail untuk mempermudah proses audit dan verifikasi jika diperlukan.</p>
                </div>
                <div class="bg-[#FFFFFF] rounded-2xl p-[28px] shadow-[0_4px_20px_rgba(229,161,0,0.05)]">
                    <div class="w-[52px] h-[52px] bg-[#FEF9E7] rounded-xl flex items-center justify-center text-[#D97706] mb-4">
                        <span class="material-symbols-outlined">trending_up</span>
                    </div>
                    <h3 class="font-semibold text-[18px] text-[#2D2A24] mb-2">Hasil Real-time</h3>
                    <p class="font-normal text-[14px] text-[#5C5648]">Pemantauan perolehan suara dilakukan secara langsung dan terbuka bagi seluruh sivitas akademika.</p>
                </div>
                <div class="bg-[#FFFFFF] rounded-2xl p-[28px] shadow-[0_4px_20px_rgba(229,161,0,0.05)]">
                    <div class="w-[52px] h-[52px] bg-[#FEF9E7] rounded-xl flex items-center justify-center text-[#D97706] mb-4">
                        <span class="material-symbols-outlined">how_to_reg</span>
                    </div>
                    <h3 class="font-semibold text-[18px] text-[#2D2A24] mb-2">Anti Double Vote</h3>
                    <p class="font-normal text-[14px] text-[#5C5648]">Sistem autentikasi berlapis memastikan setiap pemilih terdaftar hanya dapat memberikan suaranya satu kali.</p>
                </div>
                <div class="bg-[#FFFFFF] rounded-2xl p-[28px] shadow-[0_4px_20px_rgba(229,161,0,0.05)]">
                    <div class="w-[52px] h-[52px] bg-[#FEF9E7] rounded-xl flex items-center justify-center text-[#D97706] mb-4">
                        <span class="material-symbols-outlined">verified</span>
                    </div>
                    <h3 class="font-semibold text-[18px] text-[#2D2A24] mb-2">Verifikasi Mandiri</h3>
                    <p class="font-normal text-[14px] text-[#5C5648]">Pemilih dapat melakukan verifikasi secara mandiri untuk memastikan bahwa suaranya telah berhasil terekam ke dalam sistem.</p>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.partials.arutala-footer')
    @include('layouts.partials.arutala-mobile-nav')
@endsection

@push('scripts')
<script>
    (function () {
        const sections = document.querySelectorAll('section[id]');
        const links = document.querySelectorAll('.nav-link, .m-nav-link');
        if (!sections.length || !links.length) return;

        const setActive = (id) => {
            links.forEach(link => {
                const href = link.getAttribute('href') || '';
                const hash = href.includes('#') ? '#' + href.split('#').pop() : '';
                link.classList.toggle('is-active', hash === '#' + id);
            });
        };

        const observer = new IntersectionObserver((entries) => {
            const visible = entries
                .filter(e => e.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio);
            if (visible.length) setActive(visible[0].target.id);
        }, {
            rootMargin: '-40% 0px -40% 0px',
            threshold: [0, 0.25, 0.5]
        });

        sections.forEach(s => observer.observe(s));

        setActive(window.location.hash ? window.location.hash.slice(1) : 'home');
    })();

    // ===== Election Countdown =====
    (function () {
        const el = document.getElementById('election-countdown');
        if (!el) return;
        const textEl = document.getElementById('election-countdown-text');
        const endDate = new Date(el.dataset.end);
        const wrapper = el; // pill container

        const pad = n => String(n).padStart(2, '0');

        function setUrgency(level) {
            // reset palettes
            wrapper.classList.remove('bg-[#FFFBEA]', 'border-[#FCD34D]', 'bg-[#FEF3C7]', 'border-[#F59E0B]', 'bg-red-50', 'border-red-300', 'animate-pulse');
            if (level === 'critical') {
                wrapper.classList.add('bg-red-50', 'border-red-300', 'animate-pulse');
            } else if (level === 'warning') {
                wrapper.classList.add('bg-[#FEF3C7]', 'border-[#F59E0B]');
            } else {
                wrapper.classList.add('bg-[#FFFBEA]', 'border-[#FCD34D]');
            }
        }

        function tick() {
            const diff = endDate - new Date();
            if (diff <= 0) {
                textEl.textContent = 'pemilihan telah berakhir';
                setUrgency('critical');
                clearInterval(interval);
                return;
            }
            const days = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const secs = Math.floor((diff % 60000) / 1000);

            if (days >= 1) {
                textEl.textContent = `${days} hari ${hours} jam`;
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
