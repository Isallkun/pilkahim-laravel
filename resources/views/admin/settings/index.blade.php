@extends('layouts.arutala-admin')

@section('title', 'Pengaturan')
@section('breadcrumb', 'Pengaturan')

@section('content')
    <div class="flex flex-col gap-xl"
         x-data="settingsPage()">

        {{-- Header --}}
        <section class="flex flex-col md:flex-row md:items-end md:justify-between gap-md">
            <div class="flex flex-col gap-xs">
                <h1 class="font-h1 text-h1 text-primary">Pengaturan</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Visibilitas tampilan publik & operasi pembersihan data per pemilihan.</p>
            </div>

            @if ($elections->count() > 1)
                <form method="GET" action="{{ route('admin.settings.index') }}" class="flex items-center gap-2 bg-white border border-outline-variant rounded-2xl px-4 py-2 shadow-sm m-0">
                    <span class="material-symbols-outlined text-outline">filter_list</span>
                    <select name="election" onchange="this.form.submit()"
                            class="bg-transparent border-0 focus:ring-0 text-sm font-medium text-on-background py-1 pr-8">
                        @foreach ($elections as $el)
                            <option value="{{ $el->id }}" @selected($selectedElection && $el->id === $selectedElection->id)>
                                {{ $el->name }} ({{ ucfirst($el->status) }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </section>

        @if (!$selectedElection)
            <section class="bg-white p-xl rounded-2xl border border-outline-variant flex flex-col items-center text-center gap-md">
                <div class="w-16 h-16 rounded-full bg-[#FFFBEA] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#E5A100]" style="font-size: 36px;">settings</span>
                </div>
                <div>
                    <h2 class="font-h2 text-h2 text-on-background mb-1">Belum Ada Pemilihan</h2>
                    <p class="text-on-surface-variant">Buat event pemilihan dulu sebelum bisa konfigurasi.</p>
                </div>
                <a href="{{ route('admin.elections.index') }}"
                   class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors">
                    <span class="material-symbols-outlined">add</span>
                    Buat Event Pemilihan
                </a>
            </section>
        @else
            {{-- Context indicator --}}
            <section class="bg-white border border-outline-variant rounded-2xl p-md flex items-center gap-md">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0">
                    <span class="material-symbols-outlined leading-none">how_to_vote</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Konfigurasi untuk</p>
                    <p class="font-bold text-on-surface truncate">{{ $selectedElection->name }}</p>
                </div>
                @php
                    $statusBadge = match($selectedElection->status) {
                        'active' => ['bg-[#FFFBEA]', 'text-[#D97706]', 'border-[#FDE68A]', 'Aktif'],
                        'finished', 'completed' => ['bg-[#ECFDF5]', 'text-[#059669]', 'border-[#A7F3D0]', 'Selesai'],
                        default => ['bg-surface-container', 'text-on-surface-variant', 'border-outline-variant', ucfirst($selectedElection->status)],
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full {{ $statusBadge[0] }} {{ $statusBadge[1] }} border {{ $statusBadge[2] }} text-xs font-bold uppercase tracking-wider shrink-0">
                    {{ $statusBadge[3] }}
                </span>
            </section>

            {{-- ============================================================ --}}
            {{-- CARD: TAMPILAN PUBLIK                                        --}}
            {{-- ============================================================ --}}
            <section class="bg-white border border-outline-variant rounded-2xl glow-shadow overflow-hidden">
                <div class="px-lg pt-lg pb-md border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">visibility</span>
                    <h2 class="font-h2 text-h2 text-on-surface">Tampilan Publik</h2>
                </div>

                <div class="p-lg flex flex-col gap-md">
                    {{-- Toggle: Show Countdown --}}
                    <form action="{{ route('admin.settings.countdown', $selectedElection) }}" method="POST"
                          class="flex items-start justify-between gap-md p-md bg-surface-container-low rounded-xl m-0">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-start gap-md flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full {{ $selectedElection->show_countdown ? 'bg-[#ECFDF5] text-[#059669]' : 'bg-surface-container-high text-outline' }} flex items-center justify-center shrink-0 mt-0.5">
                                <span class="material-symbols-outlined leading-none">{{ $selectedElection->show_countdown ? 'timer' : 'timer_off' }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-on-surface mb-0.5">Countdown Sisa Waktu Pemilihan</h3>
                                <p class="text-sm text-on-surface-variant">
                                    Tampil di halaman <strong>landing</strong>, <strong>ballot pemilih</strong>, dan <strong>live count</strong>.
                                    Matikan kalau tidak mau bikin pressure ke pemilih.
                                </p>
                                <p class="text-xs mt-1">
                                    Status saat ini:
                                    <strong class="{{ $selectedElection->show_countdown ? 'text-[#059669]' : 'text-outline' }}">
                                        {{ $selectedElection->show_countdown ? 'TAMPIL' : 'DISEMBUNYIKAN' }}
                                    </strong>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center shrink-0 pt-1">
                            <input type="hidden" name="show_countdown" value="{{ $selectedElection->show_countdown ? '0' : '1' }}">
                            <button type="submit"
                                    title="Klik untuk toggle"
                                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-container focus:ring-offset-2
                                        {{ $selectedElection->show_countdown ? 'bg-primary-container' : 'bg-surface-container-high' }}">
                                <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                        {{ $selectedElection->show_countdown ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </form>

                    {{-- Toggle: Result Visibility (Live Count) --}}
                    @php $isPublic = $selectedElection->result_visibility === 'public'; @endphp
                    <form action="{{ route('admin.settings.result-visibility', $selectedElection) }}" method="POST"
                          class="flex items-start justify-between gap-md p-md bg-surface-container-low rounded-xl m-0">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-start gap-md flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full {{ $isPublic ? 'bg-[#ECFDF5] text-[#059669]' : 'bg-surface-container-high text-outline' }} flex items-center justify-center shrink-0 mt-0.5">
                                <span class="material-symbols-outlined leading-none">{{ $isPublic ? 'leaderboard' : 'visibility_off' }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-on-surface mb-0.5">Live Count Hasil Pemilihan</h3>
                                <p class="text-sm text-on-surface-variant">
                                    Bikin halaman <strong>live count</strong> bisa diakses publik di
                                    <code class="font-mono text-xs bg-surface-container-high px-1 py-0.5 rounded">{{ url('/results/' . $selectedElection->id) }}</code>.
                                    Update real-time tiap 5 detik. Cocok untuk projector closing acara.
                                </p>
                                <div class="text-xs mt-1 flex items-center gap-2 flex-wrap">
                                    <span>Status saat ini:
                                        <strong class="{{ $isPublic ? 'text-[#059669]' : 'text-outline' }}">
                                            {{ $isPublic ? 'PUBLIC' : 'PRIVATE' }}
                                        </strong>
                                    </span>
                                    @if ($isPublic)
                                        <a href="{{ route('results.public', $selectedElection) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 text-primary font-bold hover:underline">
                                            <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                            Buka Live Count
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center shrink-0 pt-1">
                            <input type="hidden" name="result_visibility" value="{{ $isPublic ? 'private' : 'public' }}">
                            <button type="submit"
                                    title="Klik untuk toggle"
                                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-container focus:ring-offset-2
                                        {{ $isPublic ? 'bg-primary-container' : 'bg-surface-container-high' }}">
                                <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                        {{ $isPublic ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- CARD: STATUS & LIFECYCLE                                    --}}
            {{-- ============================================================ --}}
            <section class="bg-white border border-outline-variant rounded-2xl glow-shadow overflow-hidden">
                <div class="px-lg pt-lg pb-md border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">flag</span>
                    <h2 class="font-h2 text-h2 text-on-surface">Status &amp; Lifecycle</h2>
                </div>

                <div class="p-lg flex flex-col gap-md">
                    {{-- Tutup Voting --}}
                    <div class="flex items-start justify-between gap-md p-md bg-surface-container-low rounded-xl">
                        <div class="flex items-start gap-md flex-1 min-w-0">
                            @php
                                $statusIcon = match($selectedElection->status) {
                                    'active' => ['icon' => 'play_circle', 'bg' => 'bg-[#FFFBEA]', 'text' => 'text-[#D97706]'],
                                    'finished' => ['icon' => 'task_alt', 'bg' => 'bg-[#ECFDF5]', 'text' => 'text-[#059669]'],
                                    'draft' => ['icon' => 'edit_note', 'bg' => 'bg-surface-container-high', 'text' => 'text-outline'],
                                    default => ['icon' => 'help', 'bg' => 'bg-surface-container-high', 'text' => 'text-outline'],
                                };
                            @endphp
                            <div class="w-10 h-10 rounded-full {{ $statusIcon['bg'] }} {{ $statusIcon['text'] }} flex items-center justify-center shrink-0 mt-0.5">
                                <span class="material-symbols-outlined leading-none">{{ $statusIcon['icon'] }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-on-surface mb-0.5">Status Pemilihan</h3>
                                <p class="text-sm text-on-surface-variant">
                                    Status saat ini:
                                    <strong class="{{ $statusIcon['text'] }} uppercase">{{ $selectedElection->status }}</strong>.
                                    @if ($selectedElection->status === 'active')
                                        Tutup voting sekarang akan <strong>menghentikan penerimaan suara</strong> permanen.
                                    @elseif ($selectedElection->status === 'draft')
                                        Pemilihan masih draft. Aktifkan dulu lewat <a href="{{ route('admin.elections.index') }}" class="font-bold text-primary hover:underline">Event Pemilihan</a>.
                                    @else
                                        Pemilihan sudah selesai. Suara sudah final.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if ($selectedElection->status === 'active')
                                <button type="button" @click="openCloseVoting()"
                                        class="inline-flex items-center gap-1 px-4 py-2 border border-[#D97706] text-[#D97706] rounded-lg font-bold hover:bg-[#D97706] hover:text-white transition-colors text-sm">
                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                    Tutup Voting
                                </button>
                            @else
                                <button type="button" disabled
                                        title="Status bukan active"
                                        class="inline-flex items-center gap-1 px-4 py-2 border border-outline-variant text-outline rounded-lg font-bold cursor-not-allowed opacity-60 text-sm">
                                    <span class="material-symbols-outlined text-[18px]">lock</span>
                                    Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================================ --}}
            {{-- CARD: DANGER ZONE                                            --}}
            {{-- ============================================================ --}}
            <section class="bg-white border-2 border-[#FECACA] rounded-2xl overflow-hidden">
                <div class="px-lg pt-lg pb-md border-b border-[#FECACA] bg-[#FEF2F2] flex items-center gap-2">
                    <span class="material-symbols-outlined text-error">warning</span>
                    <h2 class="font-h2 text-h2 text-error">Danger Zone</h2>
                </div>

                <div class="p-lg flex flex-col gap-md">
                    {{-- Reset Suara --}}
                    <div class="flex items-start justify-between gap-md p-md bg-[#FEF2F2] border border-[#FECACA] rounded-xl">
                        <div class="flex items-start gap-md flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-error-container text-error flex items-center justify-center shrink-0 mt-0.5">
                                <span class="material-symbols-outlined leading-none">restart_alt</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-on-surface mb-0.5">Reset Semua Suara</h3>
                                <p class="text-sm text-on-surface-variant">
                                    Hapus semua suara yang sudah masuk + reset status pemilih ke <strong>belum vote</strong>.
                                    Berguna untuk membersihkan data testing sebelum production.
                                </p>
                                @if ($voteStats)
                                    <div class="flex flex-wrap gap-3 mt-2">
                                        <span class="text-xs bg-white px-2 py-0.5 rounded border border-[#FECACA]">
                                            <span class="font-bold text-error tabular-nums">{{ number_format($voteStats['total_votes']) }}</span>
                                            <span class="text-on-surface-variant">suara</span>
                                        </span>
                                        <span class="text-xs bg-white px-2 py-0.5 rounded border border-[#FECACA]">
                                            <span class="font-bold text-error tabular-nums">{{ number_format($voteStats['voter_logs']) }}</span>
                                            <span class="text-on-surface-variant">voter logs</span>
                                        </span>
                                        <span class="text-xs bg-white px-2 py-0.5 rounded border border-[#FECACA]">
                                            <span class="font-bold text-error tabular-nums">{{ number_format($voteStats['voted_pivot']) }}</span>
                                            <span class="text-on-surface-variant">pemilih sudah-vote</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if ($selectedElection->status !== 'finished' && $voteStats && ($voteStats['total_votes'] > 0 || $voteStats['voted_pivot'] > 0))
                                <button type="button" @click="openResetVotes()"
                                        class="inline-flex items-center gap-1 px-4 py-2 border border-error text-error rounded-lg font-bold hover:bg-error hover:text-on-error transition-colors text-sm">
                                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                                    Reset
                                </button>
                            @else
                                <button type="button" disabled
                                        title="{{ $selectedElection->status === 'finished' ? 'Pemilihan sudah selesai' : 'Tidak ada suara untuk di-reset' }}"
                                        class="inline-flex items-center gap-1 px-4 py-2 border border-outline-variant text-outline rounded-lg font-bold cursor-not-allowed opacity-60 text-sm">
                                    <span class="material-symbols-outlined text-[18px]">lock</span>
                                    Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: RESET SUARA (super destructive — type-to-confirm)     --}}
        {{-- ============================================================ --}}
        @if ($selectedElection)
            <div x-show="showResetVotes" x-transition.opacity x-cloak
                 @keydown.escape.window="closeResetVotes()"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeResetVotes()"></div>
                <div x-show="showResetVotes"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border-2 border-error">

                    <div class="px-lg pt-lg pb-md flex flex-col items-center text-center gap-sm">
                        <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center">
                            <span class="material-symbols-outlined text-error" style="font-size: 32px;">restart_alt</span>
                        </div>
                        <h3 class="font-h2 text-h2 text-error">Reset Semua Suara?</h3>
                        <p class="text-on-surface-variant text-sm">
                            Aksi ini akan <strong class="text-error">menghapus permanen</strong> semua suara dan rekam partisipasi.
                            <strong>Tidak bisa dibatalkan.</strong>
                        </p>
                    </div>

                    <div class="mx-lg mb-md p-md bg-[#FEF2F2] border border-[#FECACA] rounded-2xl">
                        <p class="font-bold text-on-background text-sm">{{ $selectedElection->name }}</p>
                        @if ($voteStats)
                            <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                                <div class="bg-white rounded-lg p-2 border border-[#FECACA] text-center">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#991B1B]">Suara</div>
                                    <div class="text-error font-bold text-base tabular-nums">{{ number_format($voteStats['total_votes']) }}</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 border border-[#FECACA] text-center">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#991B1B]">Voter Logs</div>
                                    <div class="text-error font-bold text-base tabular-nums">{{ number_format($voteStats['voter_logs']) }}</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 border border-[#FECACA] text-center">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#991B1B]">Pemilih</div>
                                    <div class="text-error font-bold text-base tabular-nums">{{ number_format($voteStats['voted_pivot']) }}</div>
                                </div>
                            </div>
                        @endif
                        <ul class="text-xs text-[#991B1B] mt-3 space-y-1">
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">close</span><span>Records di tabel <code class="font-mono bg-white/50 px-1 rounded">votes</code> dihapus</span></li>
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">close</span><span>Records di <code class="font-mono bg-white/50 px-1 rounded">voter_logs</code> dihapus</span></li>
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">refresh</span><span><code class="font-mono bg-white/50 px-1 rounded">has_voted</code> di pivot di-set false</span></li>
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">check_circle</span><span>DPT (daftar pemilih) tetap utuh</span></li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.settings.destroy-votes', $selectedElection) }}" method="POST" class="px-lg pb-lg">
                        @csrf
                        @method('DELETE')

                        <label class="block text-sm font-bold text-on-surface mb-2">
                            Ketik <span class="font-mono bg-[#FEF2F2] text-error px-2 py-0.5 rounded">RESET SUARA</span> untuk konfirmasi
                        </label>
                        <input type="text" name="confirmation" x-model="resetVotesConfirm"
                               autocomplete="off" autocapitalize="characters" spellcheck="false"
                               placeholder="RESET SUARA"
                               class="w-full px-4 py-3 border-2 rounded-xl bg-white font-mono focus:outline-none focus:ring-4 transition-all"
                               :class="resetVotesConfirm === 'RESET SUARA' ? 'border-error focus:ring-error/20 text-error' : 'border-outline-variant focus:border-primary-container focus:ring-primary-container/20 text-on-surface'">

                        <div class="flex items-center gap-md mt-md">
                            <button type="button" @click="closeResetVotes()"
                                    class="flex-1 px-5 py-3 rounded-full font-bold text-on-surface-variant bg-white border border-outline-variant hover:bg-surface-container transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    :disabled="resetVotesConfirm !== 'RESET SUARA'"
                                    class="flex-1 inline-flex items-center justify-center gap-sm bg-error text-on-error px-5 py-3 rounded-full font-bold hover:bg-on-error-container transition-colors shadow-md disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-error">
                                <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- MODAL: TUTUP VOTING (semi-destructive — type-to-confirm)    --}}
            {{-- ============================================================ --}}
            <div x-show="showCloseVoting" x-transition.opacity x-cloak
                 @keydown.escape.window="closeCloseVoting()"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeCloseVoting()"></div>
                <div x-show="showCloseVoting"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border-2 border-[#D97706]">

                    <div class="px-lg pt-lg pb-md flex flex-col items-center text-center gap-sm">
                        <div class="w-16 h-16 rounded-full bg-[#FFFBEA] flex items-center justify-center">
                            <span class="material-symbols-outlined text-[#D97706]" style="font-size: 32px;">block</span>
                        </div>
                        <h3 class="font-h2 text-h2 text-[#D97706]">Tutup Voting?</h3>
                        <p class="text-on-surface-variant text-sm">
                            Setelah ditutup, pemilih <strong>tidak bisa lagi vote</strong>. Status berubah ke
                            <strong class="text-[#059669]">finished</strong>.
                        </p>
                    </div>

                    <div class="mx-lg mb-md p-md bg-[#FFFBEA] border border-[#FDE68A] rounded-2xl">
                        <p class="font-bold text-on-background text-sm">{{ $selectedElection->name }}</p>
                        @if ($voteStats)
                            <p class="text-xs text-[#B45309] mt-1">
                                Saat ini sudah masuk
                                <strong class="tabular-nums">{{ number_format($voteStats['total_votes']) }} suara</strong>
                                dari <strong class="tabular-nums">{{ number_format($voteStats['voted_pivot']) }} pemilih</strong>.
                            </p>
                        @endif
                        <ul class="text-xs text-[#B45309] mt-2 space-y-1">
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">check_circle</span><span>Suara existing tetap valid &amp; final</span></li>
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">close</span><span>Pemilih yang belum vote, tidak bisa lagi</span></li>
                            <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">refresh</span><span>Bisa publish hasil ke publik (Toggle Live Count)</span></li>
                        </ul>
                    </div>

                    <form action="{{ route('admin.settings.close-voting', $selectedElection) }}" method="POST" class="px-lg pb-lg">
                        @csrf
                        @method('PATCH')

                        <label class="block text-sm font-bold text-on-surface mb-2">
                            Ketik <span class="font-mono bg-[#FFFBEA] text-[#D97706] px-2 py-0.5 rounded">TUTUP</span> untuk konfirmasi
                        </label>
                        <input type="text" name="confirmation" x-model="closeVotingConfirm"
                               autocomplete="off" autocapitalize="characters" spellcheck="false"
                               placeholder="TUTUP"
                               class="w-full px-4 py-3 border-2 rounded-xl bg-white font-mono focus:outline-none focus:ring-4 transition-all"
                               :class="closeVotingConfirm === 'TUTUP' ? 'border-[#D97706] focus:ring-[#D97706]/20 text-[#D97706]' : 'border-outline-variant focus:border-primary-container focus:ring-primary-container/20 text-on-surface'">

                        <div class="flex items-center gap-md mt-md">
                            <button type="button" @click="closeCloseVoting()"
                                    class="flex-1 px-5 py-3 rounded-full font-bold text-on-surface-variant bg-white border border-outline-variant hover:bg-surface-container transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    :disabled="closeVotingConfirm !== 'TUTUP'"
                                    class="flex-1 inline-flex items-center justify-center gap-sm bg-[#D97706] text-white px-5 py-3 rounded-full font-bold hover:bg-[#B45309] transition-colors shadow-md disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-[#D97706]">
                                <span class="material-symbols-outlined text-[20px]">block</span>
                                Tutup Voting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function settingsPage() {
            return {
                showResetVotes: false,
                resetVotesConfirm: '',
                showCloseVoting: false,
                closeVotingConfirm: '',

                openResetVotes() {
                    this.showCloseVoting = false;
                    this.showResetVotes = true;
                    this.$nextTick(() => {
                        document.querySelector('input[name="confirmation"]')?.focus();
                    });
                },

                closeResetVotes() {
                    this.showResetVotes = false;
                    this.resetVotesConfirm = '';
                },

                openCloseVoting() {
                    this.showResetVotes = false;
                    this.showCloseVoting = true;
                    this.$nextTick(() => {
                        document.querySelector('input[name="confirmation"]')?.focus();
                    });
                },

                closeCloseVoting() {
                    this.showCloseVoting = false;
                    this.closeVotingConfirm = '';
                },
            };
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush
@endsection
