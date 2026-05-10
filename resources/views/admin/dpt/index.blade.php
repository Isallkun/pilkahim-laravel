@extends('layouts.arutala-admin')

@section('title', 'Daftar Pemilih Tetap')
@section('breadcrumb', 'Kelola DPT')

@php
    use Illuminate\Support\Js;
    use Illuminate\Support\Str;

    // Auto-reopen modal kalau validation error sebelumnya — baca old('_modal').
    // Format: 'import' atau 'create' atau 'edit:{user_id}'
    $oldModal = old('_modal', request()->query('modal'));
    $initialShowImport = $oldModal === 'import' ? 'true' : 'false';
    $initialShowCreate = $oldModal === 'create' ? 'true' : 'false';
    $initialEditId = Str::startsWith($oldModal ?? '', 'edit:') ? (int) Str::after($oldModal, 'edit:') : 'null';

    // Status guard: edit DPT hanya saat status election bukan finished
    $canMutate = $election->status !== 'finished';
@endphp

@section('content')
    <div class="flex flex-col gap-xl"
         x-data="dptPage({
            initialShowImport: {{ $initialShowImport }},
            initialShowCreate: {{ $initialShowCreate }},
            initialEditId: {{ $initialEditId }},
         })">

        {{-- Header --}}
        <section class="flex flex-col md:flex-row md:items-center md:justify-between gap-md">
            <div class="flex flex-col gap-xs">
                <h1 class="font-h1 text-h1 text-primary">Daftar Pemilih Tetap</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $election->name }} — Kelola data pemilih untuk pemilihan ini.</p>
            </div>
            @if ($canMutate)
                <div class="flex flex-wrap items-center gap-sm shrink-0">
                    <button type="button"
                            @click="openImport()"
                            class="flex items-center justify-center gap-2 px-5 py-3 bg-surface-container-high hover:bg-surface-variant text-on-surface font-bold rounded-full transition-all h-[56px]">
                        <span class="material-symbols-outlined text-[20px] leading-none">upload_file</span>
                        <span>Import Excel</span>
                    </button>
                    <button type="button"
                            @click="openCreate()"
                            class="flex items-center justify-center gap-2 px-5 py-3 bg-primary-container hover:bg-[#D97706] text-on-primary font-bold rounded-full transition-all shadow-[0_4px_12px_rgba(229,161,0,0.30)] h-[56px]">
                        <span class="material-symbols-outlined text-[20px] leading-none">person_add</span>
                        <span>Tambah Pemilih</span>
                    </button>
                </div>
            @endif
        </section>

        {{-- Stats Strip --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <div class="bg-white border border-outline-variant rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0">
                    <span class="material-symbols-outlined leading-none">groups</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Total Pemilih</p>
                    <p class="font-h2 text-h2 text-on-surface tabular-nums">{{ number_format($totalVoters) }}</p>
                </div>
            </div>

            <div class="bg-white border border-outline-variant rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full bg-[#ECFDF5] flex items-center justify-center text-[#059669] shrink-0">
                    <span class="material-symbols-outlined fill leading-none">check_circle</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Sudah Vote</p>
                    <p class="font-h2 text-h2 text-on-surface tabular-nums">{{ number_format($votedCount) }}</p>
                </div>
            </div>

            <div class="bg-white border border-outline-variant rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full bg-[#FFFBEA] flex items-center justify-center text-[#D97706] shrink-0">
                    <span class="material-symbols-outlined leading-none">schedule</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Belum Vote</p>
                    <p class="font-h2 text-h2 text-on-surface tabular-nums">{{ number_format($totalVoters - $votedCount) }}</p>
                </div>
            </div>
        </section>

        {{-- Data Table Card --}}
        <section class="bg-white border border-outline-variant rounded-2xl overflow-hidden glow-shadow">
            {{-- Toolbar --}}
            <div class="p-md border-b border-outline-variant flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-md bg-surface-container-low">
                <form method="GET" action="{{ route('admin.dpt.index', $election) }}" class="relative flex-1 sm:max-w-md w-full m-0">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Cari NIM atau Nama..."
                           class="w-full bg-white border border-outline-variant rounded-lg pl-10 pr-10 py-2 text-on-surface focus:outline-none focus:border-primary-container focus:ring-2 focus:ring-primary-container/20 transition-colors h-11">
                    @if ($search)
                        <a href="{{ route('admin.dpt.index', $election) }}" title="Reset"
                           class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-outline hover:text-error rounded-full hover:bg-surface-container">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </a>
                    @endif
                </form>
                <div class="flex items-center gap-md shrink-0">
                    <span class="text-sm text-on-surface-variant tabular-nums">
                        @if ($search)
                            <span class="font-bold">{{ $voters->total() }}</span> hasil pencarian
                        @else
                            <span class="font-bold">{{ $voters->total() }}</span> pemilih total
                        @endif
                    </span>

                    {{-- Tombol bulk delete (kalau ada attached ATAU ada orphan + bukan finished) --}}
                    @if ($canMutate && ($voters->total() > 0 || $orphanedPemilihCount > 0))
                        <button type="button"
                                @click="openDeleteAll()"
                                title="Hapus semua pemilih dari DPT + bersihkan orphan dari sistem"
                                class="inline-flex items-center gap-1 px-3 py-2 border border-[#FECACA] rounded-lg text-sm font-bold text-error hover:bg-[#FEF2F2] transition-colors">
                            <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
                            Hapus Semua
                        </button>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            @if ($voters->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container text-on-surface-variant font-label-caps text-label-caps uppercase">
                                <th class="p-4 border-b border-outline-variant">NIM</th>
                                <th class="p-4 border-b border-outline-variant">Nama</th>
                                <th class="p-4 border-b border-outline-variant hidden md:table-cell">Angkatan</th>
                                <th class="p-4 border-b border-outline-variant hidden md:table-cell text-center">Gender</th>
                                <th class="p-4 border-b border-outline-variant">Status</th>
                                <th class="p-4 border-b border-outline-variant text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-on-surface divide-y divide-outline-variant/40">
                            @foreach ($voters as $voter)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="p-4 font-mono font-bold text-primary text-sm">{{ $voter->username }}</td>
                                    <td class="p-4 font-medium">{{ $voter->name }}</td>
                                    <td class="p-4 text-on-surface-variant text-sm hidden md:table-cell">{{ $voter->angkatan ?? '—' }}</td>
                                    <td class="p-4 text-on-surface-variant text-sm hidden md:table-cell text-center">
                                        @if ($voter->gender === 'L')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-bold">L</span>
                                        @elseif ($voter->gender === 'P')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-pink-50 text-pink-700 rounded text-xs font-bold">P</span>
                                        @else
                                            <span class="text-outline">—</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($voter->pivot->has_voted)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#ECFDF5] text-[#059669] border border-[#A7F3D0] text-xs font-bold uppercase tracking-wider">
                                                <span class="material-symbols-outlined text-[14px] fill">check_circle</span>
                                                Sudah Vote
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#FFFBEA] text-[#D97706] border border-[#FDE68A] text-xs font-bold uppercase tracking-wider">
                                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                                Belum Vote
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right">
                                        @if ($canMutate)
                                            @php
                                                $voterData = Js::from([
                                                    'id' => $voter->id,
                                                    'username' => $voter->username,
                                                    'name' => $voter->name,
                                                    'angkatan' => $voter->angkatan ?? '',
                                                    'gender' => $voter->gender ?? '',
                                                    'has_voted' => (bool) $voter->pivot->has_voted,
                                                    'action_update' => route('admin.dpt.update', [$election, $voter]),
                                                    'action_delete' => route('admin.dpt.destroy', [$election, $voter]),
                                                    'action_reset' => route('admin.users.reset', $voter),
                                                ]);
                                            @endphp
                                            <div class="inline-flex items-center gap-0.5">
                                                <button type="button"
                                                        @click="openEdit({{ $voterData }})"
                                                        title="Edit pemilih"
                                                        class="p-2 text-outline hover:text-primary transition-colors rounded-full hover:bg-surface-container-low inline-flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-[18px] leading-none">edit</span>
                                                </button>
                                                <button type="button"
                                                        @click="openReset({{ $voterData }})"
                                                        title="Reset password & status"
                                                        class="p-2 text-outline hover:text-[#D97706] transition-colors rounded-full hover:bg-[#FFFBEA] inline-flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-[18px] leading-none">restart_alt</span>
                                                </button>
                                                <button type="button"
                                                        @click="openDelete({{ $voterData }})"
                                                        title="Hapus dari DPT"
                                                        class="p-2 text-outline hover:text-error transition-colors rounded-full hover:bg-error-container/30 inline-flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-[18px] leading-none">delete</span>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($voters->hasPages())
                    <div class="p-md border-t border-outline-variant flex flex-col sm:flex-row justify-between items-center gap-sm bg-surface-container-low">
                        <span class="text-sm text-on-surface-variant tabular-nums">
                            Menampilkan {{ $voters->firstItem() }}–{{ $voters->lastItem() }} dari {{ number_format($voters->total()) }}
                        </span>
                        <div>
                            {{ $voters->links() }}
                        </div>
                    </div>
                @endif
            @else
                {{-- Empty state --}}
                <div class="p-xl flex flex-col items-center text-center gap-md">
                    @if ($search)
                        <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center">
                            <span class="material-symbols-outlined text-outline" style="font-size: 36px;">search_off</span>
                        </div>
                        <div>
                            <h2 class="font-h2 text-h2 text-on-background mb-1">Tidak Ada Hasil</h2>
                            <p class="text-on-surface-variant">Pencarian "<span class="font-bold">{{ $search }}</span>" tidak menemukan pemilih.</p>
                        </div>
                        <a href="{{ route('admin.dpt.index', $election) }}"
                           class="inline-flex items-center gap-2 text-primary font-bold hover:underline">
                            <span class="material-symbols-outlined text-[20px]">refresh</span>
                            Lihat semua pemilih
                        </a>
                    @else
                        @if ($orphanedPemilihCount > 0)
                            {{-- Orphan recovery banner: ada user pemilih di DB tapi gak attached --}}
                            <div class="w-16 h-16 rounded-full bg-[#FEF3C7] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[#D97706]" style="font-size: 36px;">restore</span>
                            </div>
                            <div class="max-w-xl">
                                <h2 class="font-h2 text-h2 text-on-background mb-1">DPT Kosong, Tapi Ada {{ number_format($orphanedPemilihCount) }} Pemilih Orphan</h2>
                                <p class="text-on-surface-variant">
                                    Ditemukan <strong class="text-[#D97706] tabular-nums">{{ number_format($orphanedPemilihCount) }} user pemilih</strong> di sistem yang
                                    <strong>tidak terdaftar di pemilihan manapun</strong> (residu dari import sebelumnya).
                                    Pulihkan ke DPT ini, atau hapus dari sistem.
                                </p>
                            </div>
                            @if ($canMutate)
                                <div class="flex flex-wrap items-center justify-center gap-sm mt-2">
                                    {{-- Pulihkan: attach orphan ke election ini --}}
                                    <form action="{{ route('admin.dpt.restore-orphans', $election) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors shadow-md">
                                            <span class="material-symbols-outlined">restore</span>
                                            Pulihkan {{ number_format($orphanedPemilihCount) }} Pemilih
                                        </button>
                                    </form>
                                    {{-- Atau import baru --}}
                                    <button type="button" @click="openImport()"
                                            class="inline-flex items-center gap-2 bg-white border border-outline-variant text-on-surface px-6 py-3 rounded-full font-bold hover:bg-surface-container-low transition-colors">
                                        <span class="material-symbols-outlined">upload_file</span>
                                        Import Excel
                                    </button>
                                </div>
                                <p class="text-xs text-on-surface-variant mt-2">
                                    Atau klik <strong>Hapus Semua</strong> di toolbar atas untuk hapus orphan dari DB.
                                </p>
                            @endif
                        @else
                            {{-- Benar-benar kosong (tidak ada attached, tidak ada orphan) --}}
                            <div class="w-16 h-16 rounded-full bg-[#FFFBEA] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[#E5A100]" style="font-size: 36px;">groups</span>
                            </div>
                            <div>
                                <h2 class="font-h2 text-h2 text-on-background mb-1">Belum Ada Pemilih</h2>
                                <p class="text-on-surface-variant">Import data pemilih dari file Excel untuk memulai.</p>
                            </div>
                            @if ($canMutate)
                                <button type="button" @click="openImport()"
                                        class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors">
                                    <span class="material-symbols-outlined">upload_file</span>
                                    Import DPT Pertama
                                </button>
                            @endif
                        @endif
                    @endif
                </div>
            @endif
        </section>

        {{-- ============================================================ --}}
        {{-- MODAL: IMPORT EXCEL                                          --}}
        {{-- ============================================================ --}}
        <div x-show="showImport" x-transition.opacity x-cloak
             @keydown.escape.window="showImport = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showImport = false"></div>
            <div x-show="showImport"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <h3 class="font-h2 text-h2 text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container">upload_file</span>
                        Import DPT
                    </h3>
                    <button type="button" @click="showImport = false"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.dpt.import', $election) }}" method="POST" enctype="multipart/form-data"
                      class="flex flex-col overflow-y-auto"
                      x-data="{ fileName: '' }">
                    @csrf
                    <input type="hidden" name="_modal" value="import">

                    <div class="p-lg flex flex-col gap-md overflow-y-auto">
                        {{-- File picker --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2">
                                File Excel <span class="text-error">*</span>
                            </label>
                            <label class="block w-full p-md bg-surface-container-low border-2 border-dashed border-outline-variant rounded-2xl text-center cursor-pointer hover:border-primary-container hover:bg-[#FFFBEA] transition-all"
                                   :class="{ 'border-primary-container bg-[#FFFBEA]': fileName !== '' }">
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                       @change="fileName = $event.target.files[0]?.name || ''"
                                       class="hidden">
                                <template x-if="fileName === ''">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-outline" style="font-size: 40px;">cloud_upload</span>
                                        <p class="font-bold text-on-surface">Klik untuk pilih file</p>
                                        <p class="text-xs text-on-surface-variant">XLSX, XLS, atau CSV — maks 5 MB</p>
                                    </div>
                                </template>
                                <template x-if="fileName !== ''">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="material-symbols-outlined text-primary-container" style="font-size: 40px;">description</span>
                                        <p class="font-bold text-primary" x-text="fileName"></p>
                                        <p class="text-xs text-on-surface-variant">Klik lagi untuk ganti file</p>
                                    </div>
                                </template>
                            </label>
                            @if (old('_modal') === 'import')
                                @error('file') <p class="mt-2 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Format reference --}}
                        <div class="bg-[#FFFBEA] border border-[#FDE68A] rounded-2xl p-md">
                            <h4 class="font-bold text-sm uppercase tracking-wider text-[#B45309] mb-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[18px]">info</span>
                                Format Kolom yang Dibutuhkan
                            </h4>
                            <div class="overflow-x-auto -mx-1">
                                <table class="min-w-full text-xs border border-[#FDE68A] rounded-lg bg-white mx-1">
                                    <thead class="bg-[#FFFBEA]">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-bold text-on-surface">Username</th>
                                            <th class="px-3 py-2 text-left font-bold text-on-surface">Nama</th>
                                            <th class="px-3 py-2 text-left font-bold text-on-surface">Angkatan</th>
                                            <th class="px-3 py-2 text-left font-bold text-on-surface">Password</th>
                                            <th class="px-3 py-2 text-left font-bold text-on-surface">P/L</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-t border-[#FDE68A]">
                                            <td class="px-3 py-2 font-mono text-on-surface">001</td>
                                            <td class="px-3 py-2 text-on-surface">Abdulloh Salam</td>
                                            <td class="px-3 py-2 text-on-surface">Panjer Pambayung</td>
                                            <td class="px-3 py-2 font-mono text-on-surface">44647043</td>
                                            <td class="px-3 py-2 text-on-surface">L</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <ul class="list-disc list-inside text-xs text-[#B45309] mt-3 space-y-1">
                                <li><strong>Username</strong>: 3 digit angka (001, 002, …)</li>
                                <li><strong>Password</strong>: 8 digit angka (akan di-hash saat import)</li>
                                <li><strong>Angkatan</strong>: Panjer Pambayung / Bubuhan Danadyaksa / Arjuna Pangarsa</li>
                                <li><strong>P/L</strong>: L (Laki-laki) atau P (Perempuan)</li>
                                <li>Username yang sudah ada akan dilewati (skip, tidak di-overwrite)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant shrink-0">
                        <button type="button" @click="showImport = false"
                                class="px-5 py-3 rounded-full font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-sm bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors shadow-md">
                            <span class="material-symbols-outlined text-[20px]">cloud_upload</span>
                            Upload &amp; Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: RESET USER (destructive)                              --}}
        {{-- ============================================================ --}}
        <div x-show="resettingUser !== null" x-transition.opacity x-cloak
             @keydown.escape.window="resettingUser = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="resettingUser = null"></div>
            <div x-show="resettingUser !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">

                <div class="px-lg pt-lg pb-md flex flex-col items-center text-center gap-sm">
                    <div class="w-16 h-16 rounded-full bg-[#FEF3C7] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#D97706]" style="font-size: 32px;">restart_alt</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-on-background">Reset Pemilih?</h3>
                    <p class="text-on-surface-variant text-sm">
                        Password akan di-set ulang ke <strong>password awal</strong> dan status voting akan dikembalikan ke <strong>belum vote</strong>.
                    </p>
                </div>

                <div class="mx-lg my-md p-md bg-surface-container-low border border-outline-variant rounded-2xl flex items-center gap-md">
                    <div class="w-12 h-12 rounded-full bg-primary-container/20 flex items-center justify-center text-primary-container shrink-0">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-mono font-bold text-primary text-sm" x-text="resettingUser?.username"></p>
                        <p class="font-bold text-on-background truncate" x-text="resettingUser?.name"></p>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            Status saat ini:
                            <span x-show="resettingUser?.has_voted" class="text-[#059669] font-bold">Sudah Vote</span>
                            <span x-show="!resettingUser?.has_voted" class="text-[#D97706] font-bold">Belum Vote</span>
                        </p>
                    </div>
                </div>

                {{-- Warning kalau user sudah vote --}}
                <template x-if="resettingUser?.has_voted">
                    <div class="mx-lg mb-md p-3 bg-[#FEF2F2] border border-[#FECACA] rounded-xl flex gap-2">
                        <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">warning</span>
                        <p class="text-xs text-[#991B1B] font-medium">
                            Pemilih ini <strong>sudah voting</strong>. Reset akan menghapus catatan partisipasi-nya — gunakan dengan hati-hati untuk koreksi data, bukan operasi rutin.
                        </p>
                    </div>
                </template>

                <form x-ref="resetForm" :action="resettingUser?.action_reset" method="POST" class="hidden">
                    @csrf
                </form>

                <div class="flex items-center gap-md p-lg bg-surface-container-low border-t border-outline-variant">
                    <button type="button" @click="resettingUser = null"
                            class="flex-1 px-5 py-3 rounded-full font-bold text-on-surface-variant bg-white border border-outline-variant hover:bg-surface-container transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="$refs.resetForm.submit()"
                            class="flex-1 inline-flex items-center justify-center gap-sm bg-[#D97706] text-white px-5 py-3 rounded-full font-bold hover:bg-[#B45309] transition-colors shadow-md">
                        <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: CREATE PEMILIH                                        --}}
        {{-- ============================================================ --}}
        <div x-show="showCreate" x-transition.opacity x-cloak
             @keydown.escape.window="showCreate = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showCreate = false"></div>
            <div x-show="showCreate"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <h3 class="font-h2 text-h2 text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container">person_add</span>
                        Tambah Pemilih
                    </h3>
                    <button type="button" @click="showCreate = false"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.dpt.store', $election) }}" method="POST" class="flex flex-col overflow-y-auto">
                    @csrf
                    <input type="hidden" name="_modal" value="create">

                    <div class="p-lg flex flex-col gap-md overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">
                                    NIM/Username <span class="text-error">*</span>
                                </label>
                                <input type="text" name="username" maxlength="3" pattern="[0-9]{3}" required
                                       value="{{ old('_modal') === 'create' ? old('username') : '' }}"
                                       placeholder="001"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 font-mono">
                                <p class="mt-1 text-xs text-outline">3 digit angka</p>
                                @if (old('_modal') === 'create')
                                    @error('username') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">
                                    Password <span class="text-error">*</span>
                                </label>
                                <input type="text" name="password" required minlength="6"
                                       value="{{ old('_modal') === 'create' ? old('password') : '' }}"
                                       placeholder="44647043"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 font-mono">
                                <p class="mt-1 text-xs text-outline">Min 6 karakter, akan di-hash</p>
                                @if (old('_modal') === 'create')
                                    @error('password') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Nama Lengkap <span class="text-error">*</span>
                            </label>
                            <input type="text" name="name" required
                                   value="{{ old('_modal') === 'create' ? old('name') : '' }}"
                                   placeholder="Abdulloh Salam"
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20">
                            @if (old('_modal') === 'create')
                                @error('name') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-on-surface mb-1">
                                    Angkatan <span class="text-error">*</span>
                                </label>
                                <input type="text" name="angkatan" required list="angkatan-options"
                                       value="{{ old('_modal') === 'create' ? old('angkatan') : '' }}"
                                       placeholder="Panjer Pambayung"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20">
                                <datalist id="angkatan-options">
                                    <option value="Panjer Pambayung">
                                    <option value="Bubuhan Danadyaksa">
                                    <option value="Arjuna Pangarsa">
                                </datalist>
                                @if (old('_modal') === 'create')
                                    @error('angkatan') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">Gender</label>
                                <select name="gender"
                                        class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20">
                                    <option value="">—</option>
                                    <option value="L" @selected(old('_modal') === 'create' && old('gender') === 'L')>L (Laki-laki)</option>
                                    <option value="P" @selected(old('_modal') === 'create' && old('gender') === 'P')>P (Perempuan)</option>
                                </select>
                                @if (old('_modal') === 'create')
                                    @error('gender') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant shrink-0">
                        <button type="button" @click="showCreate = false"
                                class="px-5 py-3 rounded-full font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-sm bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors shadow-md">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Tambah Pemilih
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: EDIT PEMILIH                                          --}}
        {{-- ============================================================ --}}
        <div x-show="editingId !== null" x-transition.opacity x-cloak
             @keydown.escape.window="editingId = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="editingId = null"></div>
            <div x-show="editingId !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <h3 class="font-h2 text-h2 text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container">edit</span>
                        Edit Pemilih
                    </h3>
                    <button type="button" @click="editingId = null"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="editForm.action_update" method="POST" class="flex flex-col overflow-y-auto">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" :value="`edit:${editingId}`">

                    <div class="p-lg flex flex-col gap-md overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">
                                    NIM/Username <span class="text-error">*</span>
                                </label>
                                <input type="text" name="username" maxlength="3" pattern="[0-9]{3}" required
                                       x-model="editForm.username"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 font-mono">
                                @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                    @error('username') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">Password Baru</label>
                                <input type="text" name="password" minlength="6"
                                       placeholder="Kosongkan untuk tidak mengubah"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 font-mono">
                                <p class="mt-1 text-xs text-outline">Kosongkan = password lama tetap dipakai</p>
                                @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                    @error('password') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Nama Lengkap <span class="text-error">*</span>
                            </label>
                            <input type="text" name="name" required x-model="editForm.name"
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20">
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('name') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-on-surface mb-1">
                                    Angkatan <span class="text-error">*</span>
                                </label>
                                <input type="text" name="angkatan" required list="angkatan-edit-options"
                                       x-model="editForm.angkatan"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20">
                                <datalist id="angkatan-edit-options">
                                    <option value="Panjer Pambayung">
                                    <option value="Bubuhan Danadyaksa">
                                    <option value="Arjuna Pangarsa">
                                </datalist>
                                @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                    @error('angkatan') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">Gender</label>
                                <select name="gender" x-model="editForm.gender"
                                        class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20">
                                    <option value="">—</option>
                                    <option value="L">L (Laki-laki)</option>
                                    <option value="P">P (Perempuan)</option>
                                </select>
                                @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                    @error('gender') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant shrink-0">
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

        {{-- ============================================================ --}}
        {{-- MODAL: DELETE PEMILIH (destructive)                          --}}
        {{-- ============================================================ --}}
        <div x-show="deletingUser !== null" x-transition.opacity x-cloak
             @keydown.escape.window="deletingUser = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deletingUser = null"></div>
            <div x-show="deletingUser !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">

                <div class="px-lg pt-lg pb-md flex flex-col items-center text-center gap-sm">
                    <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-error" style="font-size: 32px;">delete</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-on-background">Hapus Pemilih dari DPT?</h3>
                    <p class="text-on-surface-variant text-sm">
                        Pemilih akan <strong>dilepas dari pemilihan ini</strong>. Akun user tetap ada di sistem (bisa terdaftar di pemilihan lain).
                    </p>
                </div>

                <div class="mx-lg my-md p-md bg-surface-container-low border border-outline-variant rounded-2xl flex items-center gap-md">
                    <div class="w-12 h-12 rounded-full bg-primary-container/20 flex items-center justify-center text-primary-container shrink-0">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-mono font-bold text-primary text-sm" x-text="deletingUser?.username"></p>
                        <p class="font-bold text-on-background truncate" x-text="deletingUser?.name"></p>
                    </div>
                </div>

                <template x-if="deletingUser?.has_voted">
                    <div class="mx-lg mb-md p-3 bg-[#FEF2F2] border border-[#FECACA] rounded-xl flex gap-2">
                        <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">warning</span>
                        <p class="text-xs text-[#991B1B] font-medium">
                            Pemilih ini <strong>sudah voting</strong>. Catatan suaranya tetap valid (anonim, tidak terkait user) tapi rekam partisipasi akan hilang dari DPT.
                        </p>
                    </div>
                </template>

                <form x-ref="deleteForm" :action="deletingUser?.action_delete" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="flex items-center gap-md p-lg bg-surface-container-low border-t border-outline-variant">
                    <button type="button" @click="deletingUser = null"
                            class="flex-1 px-5 py-3 rounded-full font-bold text-on-surface-variant bg-white border border-outline-variant hover:bg-surface-container transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="$refs.deleteForm.submit()"
                            class="flex-1 inline-flex items-center justify-center gap-sm bg-error text-on-error px-5 py-3 rounded-full font-bold hover:bg-on-error-container transition-colors shadow-md">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: DELETE ALL (super destructive — type-to-confirm)      --}}
        {{-- ============================================================ --}}
        <div x-show="showDeleteAll" x-transition.opacity x-cloak
             @keydown.escape.window="closeDeleteAll()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeDeleteAll()"></div>
            <div x-show="showDeleteAll"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border-2 border-error">

                @php
                    $totalToDelete = $voters->total() + $orphanedPemilihCount;
                @endphp
                <div class="px-lg pt-lg pb-md flex flex-col items-center text-center gap-sm">
                    <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-error" style="font-size: 32px;">delete_forever</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-error">Hapus SEMUA Pemilih?</h3>
                    <p class="text-on-surface-variant text-sm">
                        Aksi ini akan menghapus <strong class="text-error tabular-nums">{{ number_format($totalToDelete) }} pemilih</strong>
                        dari sistem. <strong>Tidak bisa dibatalkan.</strong>
                    </p>
                </div>

                {{-- Breakdown jumlah --}}
                <div class="mx-lg mb-md p-md bg-[#FEF2F2] border border-[#FECACA] rounded-2xl">
                    <p class="font-bold text-on-background text-sm">{{ $election->name }}</p>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-white rounded-lg p-2 border border-[#FECACA]">
                            <div class="text-[#991B1B] font-bold uppercase tracking-wider text-[10px]">Di DPT ini</div>
                            <div class="text-error font-bold text-lg tabular-nums">{{ number_format($voters->total()) }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-2 border border-[#FECACA]">
                            <div class="text-[#991B1B] font-bold uppercase tracking-wider text-[10px]">Orphan</div>
                            <div class="text-error font-bold text-lg tabular-nums">{{ number_format($orphanedPemilihCount) }}</div>
                        </div>
                    </div>
                    <ul class="text-xs text-[#991B1B] mt-3 space-y-1">
                        <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">close</span><span>Akun user (role pemilih) <strong>dihapus dari DB</strong></span></li>
                        <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">close</span><span>Voter logs (rekam partisipasi) ikut dihapus</span></li>
                        <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">check_circle</span><span>Admin/Panitia/Saksi <strong>AMAN</strong> (beda role)</span></li>
                        <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">check_circle</span><span>Pemilih yang juga terdaftar di pemilihan lain → cuma dilepas dari sini</span></li>
                        <li class="flex gap-1.5"><span class="material-symbols-outlined text-[14px] mt-0.5">check_circle</span><span>Suara yang sudah masuk tetap valid (anonim)</span></li>
                    </ul>
                </div>

                {{-- Type-to-confirm --}}
                <form action="{{ route('admin.dpt.destroy-all', $election) }}" method="POST" class="px-lg pb-lg">
                    @csrf
                    @method('DELETE')

                    <label class="block text-sm font-bold text-on-surface mb-2">
                        Ketik <span class="font-mono bg-[#FEF2F2] text-error px-2 py-0.5 rounded">HAPUS SEMUA</span> untuk konfirmasi
                    </label>
                    <input type="text" name="confirmation" x-model="deleteAllConfirm"
                           autocomplete="off" autocapitalize="characters" spellcheck="false"
                           placeholder="HAPUS SEMUA"
                           class="w-full px-4 py-3 border-2 rounded-xl bg-white font-mono focus:outline-none focus:ring-4 transition-all"
                           :class="deleteAllConfirm === 'HAPUS SEMUA' ? 'border-error focus:ring-error/20 text-error' : 'border-outline-variant focus:border-primary-container focus:ring-primary-container/20 text-on-surface'">

                    <div class="flex items-center gap-md mt-md">
                        <button type="button" @click="closeDeleteAll()"
                                class="flex-1 px-5 py-3 rounded-full font-bold text-on-surface-variant bg-white border border-outline-variant hover:bg-surface-container transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="deleteAllConfirm !== 'HAPUS SEMUA'"
                                class="flex-1 inline-flex items-center justify-center gap-sm bg-error text-on-error px-5 py-3 rounded-full font-bold hover:bg-on-error-container transition-colors shadow-md disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-error">
                            <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                            Hapus Semua
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function dptPage(initial) {
            return {
                showImport: initial.initialShowImport,
                showCreate: initial.initialShowCreate,
                editingId: initial.initialEditId,
                resettingUser: null,
                deletingUser: null,
                showDeleteAll: false,
                deleteAllConfirm: '',

                // Edit form state — populated by openEdit()
                editForm: {
                    username: @json(old('username', '')),
                    name: @json(old('name', '')),
                    angkatan: @json(old('angkatan', '')),
                    gender: @json(old('gender', '')),
                    action_update: '',
                },

                _closeAll() {
                    this.showImport = false;
                    this.showCreate = false;
                    this.editingId = null;
                    this.resettingUser = null;
                    this.deletingUser = null;
                    this.showDeleteAll = false;
                    this.deleteAllConfirm = '';
                },

                openImport() {
                    this._closeAll();
                    this.showImport = true;
                },

                openCreate() {
                    this._closeAll();
                    this.showCreate = true;
                },

                openEdit(user) {
                    this._closeAll();
                    this.editingId = user.id;
                    @if (!Str::startsWith(old('_modal') ?? '', 'edit:'))
                    this.editForm = {
                        username: user.username,
                        name: user.name,
                        angkatan: user.angkatan,
                        gender: user.gender,
                        action_update: user.action_update,
                    };
                    @else
                    // Tetap pakai old() values untuk field, tapi action_update perlu di-set dari row data
                    this.editForm.action_update = user.action_update;
                    @endif
                },

                openReset(user) {
                    this._closeAll();
                    this.resettingUser = user;
                },

                openDelete(user) {
                    this._closeAll();
                    this.deletingUser = user;
                },

                openDeleteAll() {
                    this._closeAll();
                    this.showDeleteAll = true;
                    // Auto-focus input setelah modal tampil
                    this.$nextTick(() => {
                        document.querySelector('input[name="confirmation"]')?.focus();
                    });
                },

                closeDeleteAll() {
                    this.showDeleteAll = false;
                    this.deleteAllConfirm = '';
                },
            };
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        /* Pagination link styling — match design system */
        nav[role="navigation"] .relative.inline-flex {
            border: 1px solid #d5c4ad;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            color: #514533;
            font-weight: 500;
            transition: all .15s;
        }
        nav[role="navigation"] .relative.inline-flex:hover {
            background: #faf3e9;
            border-color: #e5a100;
            color: #7e5700;
        }
        nav[role="navigation"] .bg-white.border-gray-300 {
            background: #e5a100;
            color: #fff;
            border-color: #e5a100;
        }
        nav[role="navigation"] span.relative.inline-flex {
            color: #b6a583;
        }
    </style>
    @endpush
@endsection
