@extends('layouts.arutala-admin')

@section('title', 'Event Pemilihan')
@section('breadcrumb', 'Event Pemilihan')

@php
    use Illuminate\Support\Js;
    use Illuminate\Support\Str;

    // Pisahkan: yang "berjalan" (active) tampil sebagai Hero card, sisanya jadi history.
    $activeElection = $elections->firstWhere('status', 'active');
    $historyElections = $elections->reject(fn ($e) => $e->status === 'active')->values();

    // Helper kecil untuk hitung pemenang (candidate dengan vote terbanyak) — null kalau tidak ada vote.
    $winnerOf = function ($election) {
        $top = \App\Models\Vote::where('election_id', $election->id)
            ->selectRaw('candidate_id, COUNT(*) as total')
            ->groupBy('candidate_id')
            ->orderByDesc('total')
            ->first();
        if (!$top) return null;
        return \App\Models\Candidate::find($top->candidate_id);
    };

    // Auto-reopen modal kalau validation error sebelumnya — baca old('_modal').
    // Format: 'create' atau 'edit:{id}'
    $oldModal = old('_modal', request()->query('modal'));
    $initialShowCreate = $oldModal === 'create' ? 'true' : 'false';
    $initialEditId = Str::startsWith($oldModal ?? '', 'edit:') ? (int) Str::after($oldModal, 'edit:') : 'null';
@endphp

@section('content')
    <div class="flex flex-col gap-xl"
         x-data="electionsPage({
            initialShowCreate: {{ $initialShowCreate }},
            initialEditId: {{ $initialEditId }},
         })">

        {{-- Header --}}
        <section class="flex flex-col md:flex-row md:items-center md:justify-between gap-md">
            <div class="flex flex-col gap-xs">
                <h1 class="font-h1 text-h1 text-primary">Event Pemilihan</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant">Kelola periode dan jadwal pemilihan</p>
            </div>
            <button type="button"
                    @click="openCreate()"
                    class="inline-flex items-center justify-center gap-sm bg-[#E5A100] text-white hover:bg-[#D97706] transition-colors rounded-full px-5 py-3 font-bold shadow-[0_4px_20px_0_rgba(229,161,0,0.30)] shrink-0">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Buat Event Baru
            </button>
        </section>

        {{-- Hero — Active election --}}
        @if ($activeElection)
            <section class="bg-white rounded-2xl border border-outline-variant p-lg md:p-xl shadow-[0_8px_30px_-4px_rgba(229,161,0,0.15)] relative overflow-hidden flex flex-col gap-md">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-fixed/30 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-start justify-between z-10">
                    <div class="flex flex-col gap-sm">
                        <span class="inline-flex items-center px-3 py-1 bg-secondary-container text-on-secondary-container font-label-caps text-label-caps rounded-full w-max gap-sm">
                            <span class="material-symbols-outlined text-[16px] fill">radio_button_checked</span>
                            Sedang Berjalan
                        </span>
                        <h2 class="font-h2 text-h2 text-primary">{{ $activeElection->name }}</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-md z-10 mt-sm">
                    <div class="bg-surface-container-low rounded-xl p-md border border-surface-variant flex flex-col gap-xs">
                        <span class="font-body-md text-on-surface-variant text-sm">Mulai</span>
                        <div class="flex items-center gap-sm text-on-surface font-medium">
                            <span class="material-symbols-outlined text-outline">calendar_today</span>
                            {{ $activeElection->start_date->locale('id')->translatedFormat('d M Y, H:i') }}
                        </div>
                    </div>
                    <div class="bg-surface-container-low rounded-xl p-md border border-surface-variant flex flex-col gap-xs">
                        <span class="font-body-md text-on-surface-variant text-sm">Selesai</span>
                        <div class="flex items-center gap-sm text-on-surface font-medium">
                            <span class="material-symbols-outlined text-outline">event_available</span>
                            {{ $activeElection->end_date->locale('id')->translatedFormat('d M Y, H:i') }}
                        </div>
                    </div>
                    <div class="bg-surface-container-low rounded-xl p-md border border-surface-variant flex flex-col gap-xs">
                        <span class="font-body-md text-on-surface-variant text-sm">Total Kandidat</span>
                        <div class="flex items-center gap-sm text-on-surface font-medium">
                            <span class="material-symbols-outlined text-outline">groups</span>
                            {{ $activeElection->candidates_count }} Pasangan Calon
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-md z-10 bg-[#FFFBEA] border border-[#FDE68A] rounded-xl p-md">
                    <div class="text-center md:text-left">
                        <div class="font-bold text-2xl text-primary tabular-nums">{{ number_format($activeElection->voters_count) }}</div>
                        <div class="text-xs text-on-surface-variant uppercase tracking-wider font-bold">Total DPT</div>
                    </div>
                    <div class="text-center md:text-left border-l border-[#FDE68A] pl-md">
                        <div class="font-bold text-2xl text-primary tabular-nums">{{ number_format($activeElection->voted_voters_count) }}</div>
                        <div class="text-xs text-on-surface-variant uppercase tracking-wider font-bold">Sudah Vote</div>
                    </div>
                    <div class="text-center md:text-left border-l border-[#FDE68A] pl-md">
                        @php $turnout = $activeElection->voters_count > 0 ? round(($activeElection->voted_voters_count / $activeElection->voters_count) * 100) : 0; @endphp
                        <div class="font-bold text-2xl text-primary tabular-nums">{{ $turnout }}%</div>
                        <div class="text-xs text-on-surface-variant uppercase tracking-wider font-bold">Turnout</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-md z-10 mt-md">
                    @php
                        $editData = Js::from([
                            'id' => $activeElection->id,
                            'name' => $activeElection->name,
                            'start_date' => $activeElection->start_date->format('Y-m-d\TH:i'),
                            'end_date' => $activeElection->end_date->format('Y-m-d\TH:i'),
                            'status' => $activeElection->status,
                        ]);
                    @endphp
                    <button type="button"
                            @click="openEdit({{ $editData }})"
                            class="bg-primary-container text-on-primary-container hover:bg-tertiary-container transition-colors rounded-full px-6 py-3 font-label-caps text-label-caps flex items-center gap-sm h-[56px]">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                        Edit
                    </button>
                    <a href="{{ route('admin.elections.candidates.index', $activeElection) }}"
                       class="bg-surface-container text-on-surface hover:bg-surface-variant transition-colors rounded-full px-6 py-3 font-label-caps text-label-caps flex items-center gap-sm h-[56px]">
                        <span class="material-symbols-outlined text-[20px]">groups</span>
                        Kandidat
                    </a>
                    <a href="{{ route('admin.dpt.index', $activeElection) }}"
                       class="bg-surface-container text-on-surface hover:bg-surface-variant transition-colors rounded-full px-6 py-3 font-label-caps text-label-caps flex items-center gap-sm h-[56px]">
                        <span class="material-symbols-outlined text-[20px]">badge</span>
                        DPT
                    </a>
                </div>
            </section>
        @else
            <section class="bg-white rounded-2xl border border-outline-variant p-xl flex flex-col items-center text-center gap-md">
                <div class="w-16 h-16 rounded-full bg-[#FFFBEA] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#E5A100]" style="font-size: 36px;">event_busy</span>
                </div>
                <div>
                    <h2 class="font-h2 text-h2 text-on-background mb-1">Tidak Ada Pemilihan Aktif</h2>
                    <p class="text-on-surface-variant">Mulai event baru atau aktifkan event yang sudah dibuat.</p>
                </div>
                <button type="button"
                        @click="openCreate()"
                        class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors">
                    <span class="material-symbols-outlined">add</span>
                    Buat Event Baru
                </button>
            </section>
        @endif

        {{-- History --}}
        @if ($historyElections->count() > 0)
            <section class="flex flex-col gap-lg mt-md">
                <h2 class="font-h2 text-h2 text-primary">Riwayat &amp; Draft Pemilihan</h2>

                <div class="flex flex-col gap-md">
                    <div class="hidden md:grid grid-cols-12 gap-md px-md py-sm font-label-caps text-label-caps text-outline border-b border-outline-variant">
                        <div class="col-span-3">Nama Pemilihan</div>
                        <div class="col-span-3">Tanggal Pelaksanaan</div>
                        <div class="col-span-2">Status</div>
                        <div class="col-span-2">Partisipasi</div>
                        <div class="col-span-2 text-right">Aksi</div>
                    </div>

                    @foreach ($historyElections as $election)
                        @php
                            $turnoutPct = $election->voters_count > 0
                                ? round(($election->voted_voters_count / $election->voters_count) * 100)
                                : 0;
                            $winner = in_array($election->status, ['completed', 'finished']) ? $winnerOf($election) : null;

                            $statusBadge = match($election->status) {
                                'completed', 'finished' => ['bg' => 'bg-[#ECFDF5]', 'text' => 'text-[#059669]', 'border' => 'border-[#A7F3D0]', 'label' => 'Selesai'],
                                'draft' => ['bg' => 'bg-surface-container', 'text' => 'text-on-surface-variant', 'border' => 'border-outline-variant', 'label' => 'Draft'],
                                default => ['bg' => 'bg-surface-container', 'text' => 'text-on-surface-variant', 'border' => 'border-outline-variant', 'label' => ucfirst($election->status)],
                            };
                        @endphp

                        <div class="bg-white rounded-2xl border border-outline-variant p-md flex flex-col md:grid md:grid-cols-12 gap-md items-start md:items-center hover:shadow-[0_4px_12px_0_rgba(229,161,0,0.10)] transition-shadow">
                            <div class="flex items-center gap-sm w-full md:col-span-3 min-w-0">
                                <span class="md:hidden font-label-caps text-outline text-xs w-24 shrink-0">Nama</span>
                                <span class="font-body-md text-on-surface font-bold truncate">{{ $election->name }}</span>
                            </div>

                            <div class="flex items-center gap-sm w-full md:col-span-3">
                                <span class="md:hidden font-label-caps text-outline text-xs w-24 shrink-0">Tanggal</span>
                                <span class="font-body-md text-on-surface-variant text-sm">
                                    {{ $election->start_date->locale('id')->translatedFormat('d M Y') }}
                                    &mdash;
                                    {{ $election->end_date->locale('id')->translatedFormat('d M Y') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-sm w-full md:col-span-2">
                                <span class="md:hidden font-label-caps text-outline text-xs w-24 shrink-0">Status</span>
                                <span class="inline-flex items-center px-3 py-1 {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} border {{ $statusBadge['border'] }} rounded-full text-xs font-bold uppercase tracking-wider">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </div>

                            <div class="flex items-center gap-sm w-full md:col-span-2">
                                <span class="md:hidden font-label-caps text-outline text-xs w-24 shrink-0">Partisipasi</span>
                                <div class="flex flex-col gap-xs">
                                    <span class="font-body-md text-on-surface tabular-nums">{{ number_format($election->voted_voters_count) }} suara</span>
                                    <span class="inline-block px-2 py-0.5 bg-surface-container-high text-on-surface-variant rounded text-xs font-medium w-max tabular-nums">
                                        Turnout {{ $turnoutPct }}%
                                    </span>
                                    @if ($winner)
                                        <span class="text-xs text-[#5C5648] font-medium mt-1 inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[#E5A100] text-[14px] fill">workspace_premium</span>
                                            {{ $winner->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-start md:justify-end gap-1 w-full md:col-span-2">
                                <span class="md:hidden font-label-caps text-outline text-xs w-24 shrink-0">Aksi</span>
                                @php
                                    $rowEditData = Js::from([
                                        'id' => $election->id,
                                        'name' => $election->name,
                                        'start_date' => $election->start_date->format('Y-m-d\TH:i'),
                                        'end_date' => $election->end_date->format('Y-m-d\TH:i'),
                                        'status' => $election->status,
                                    ]);
                                @endphp
                                <button type="button"
                                        @click="openEdit({{ $rowEditData }})"
                                        aria-label="Edit"
                                        title="Edit"
                                        class="p-2 rounded-full hover:bg-surface-container-low text-outline hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <a href="{{ route('admin.elections.candidates.index', $election) }}"
                                   aria-label="Kandidat" title="Kandidat"
                                   class="p-2 rounded-full hover:bg-surface-container-low text-outline hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">groups</span>
                                </a>
                                <a href="{{ route('admin.dpt.index', $election) }}"
                                   aria-label="DPT" title="DPT"
                                   class="p-2 rounded-full hover:bg-surface-container-low text-outline hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">badge</span>
                                </a>
                                @if (in_array($election->status, ['finished', 'completed']))
                                    <form action="{{ route('admin.elections.toggle-results', $election) }}" method="POST" class="inline m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                aria-label="{{ $election->result_visibility === 'private' ? 'Publikasi Hasil' : 'Sembunyikan Hasil' }}"
                                                title="{{ $election->result_visibility === 'private' ? 'Publikasi Hasil' : 'Sembunyikan Hasil' }}"
                                                class="p-2 rounded-full hover:bg-surface-container-low text-outline hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined">
                                                {{ $election->result_visibility === 'private' ? 'visibility' : 'visibility_off' }}
                                            </span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: CREATE                                                --}}
        {{-- ============================================================ --}}
        <div x-show="showCreate"
             x-transition.opacity
             x-cloak
             @keydown.escape.window="showCreate = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showCreate = false"></div>
            <div x-show="showCreate"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant">
                    <h3 class="font-h2 text-h2 text-primary">Buat Event Pemilihan</h3>
                    <button type="button" @click="showCreate = false"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.elections.store') }}" method="POST" class="flex flex-col">
                    @csrf
                    <input type="hidden" name="_modal" value="create">

                    <div class="p-lg flex flex-col gap-md">
                        {{-- Nama --}}
                        <div>
                            <label for="create-name" class="block text-sm font-bold text-on-surface mb-1">
                                Nama Pemilihan <span class="text-error">*</span>
                            </label>
                            <input type="text" id="create-name" name="name"
                                   value="{{ old('_modal') === 'create' ? old('name') : '' }}"
                                   placeholder="Contoh: Pemilihan Ketua HIMA 2026"
                                   required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (old('_modal') === 'create')
                                @error('name')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        {{-- Tanggal mulai --}}
                        <div>
                            <label for="create-start" class="block text-sm font-bold text-on-surface mb-1">
                                Tanggal Mulai <span class="text-error">*</span>
                            </label>
                            <input type="datetime-local" id="create-start" name="start_date"
                                   value="{{ old('_modal') === 'create' ? old('start_date') : '' }}"
                                   required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (old('_modal') === 'create')
                                @error('start_date')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        {{-- Tanggal selesai --}}
                        <div>
                            <label for="create-end" class="block text-sm font-bold text-on-surface mb-1">
                                Tanggal Selesai <span class="text-error">*</span>
                            </label>
                            <input type="datetime-local" id="create-end" name="end_date"
                                   value="{{ old('_modal') === 'create' ? old('end_date') : '' }}"
                                   required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (old('_modal') === 'create')
                                @error('end_date')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>

                    {{-- Footer actions --}}
                    <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant">
                        <button type="button" @click="showCreate = false"
                                class="px-5 py-3 rounded-full font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-sm bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors shadow-md">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: EDIT                                                  --}}
        {{-- ============================================================ --}}
        <div x-show="editingId !== null"
             x-transition.opacity
             x-cloak
             @keydown.escape.window="editingId = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="editingId = null"></div>
            <div x-show="editingId !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant">
                    <h3 class="font-h2 text-h2 text-primary">Edit Event Pemilihan</h3>
                    <button type="button" @click="editingId = null"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="`{{ url('admin/elections') }}/${editingId}`" method="POST" class="flex flex-col">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" :value="`edit:${editingId}`">

                    <div class="p-lg flex flex-col gap-md">
                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Nama Pemilihan <span class="text-error">*</span>
                            </label>
                            <input type="text" name="name" x-model="form.name" required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('name')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        {{-- Tanggal mulai --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Tanggal Mulai <span class="text-error">*</span>
                            </label>
                            <input type="datetime-local" name="start_date" x-model="form.start_date" required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('start_date')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        {{-- Tanggal selesai --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Tanggal Selesai <span class="text-error">*</span>
                            </label>
                            <input type="datetime-local" name="end_date" x-model="form.end_date" required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('end_date')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Status <span class="text-error">*</span>
                            </label>
                            <select name="status" x-model="form.status" required
                                    class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                                <option value="draft">Draft (belum dibuka)</option>
                                <option value="active">Active (sedang berjalan)</option>
                                <option value="finished">Finished (selesai)</option>
                            </select>
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('status')
                                    <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant">
                        <button type="button" @click="editingId = null"
                                class="px-5 py-3 rounded-full font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-sm bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors shadow-md">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function electionsPage({ initialShowCreate, initialEditId }) {
            return {
                showCreate: initialShowCreate,
                editingId: initialEditId,
                form: {
                    name: @json(old('name', '')),
                    start_date: @json(old('start_date', '')),
                    end_date: @json(old('end_date', '')),
                    status: @json(old('status', 'draft')),
                },
                openCreate() {
                    this.editingId = null;
                    this.showCreate = true;
                },
                openEdit(election) {
                    this.showCreate = false;
                    this.editingId = election.id;
                    // Kalau bukan dari error reopen, populate dengan data row.
                    // Kalau dari error reopen (initialEditId === election.id), tetap pakai old() values.
                    @if (!Str::startsWith(old('_modal') ?? '', 'edit:'))
                    this.form = {
                        name: election.name,
                        start_date: election.start_date,
                        end_date: election.end_date,
                        status: election.status,
                    };
                    @endif
                },
            };
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush
@endsection
