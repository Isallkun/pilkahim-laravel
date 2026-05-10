@extends('layouts.arutala-admin')

@section('title', 'Kelola Kandidat')
@section('breadcrumb', 'Kelola Kandidat')

@php
    use Illuminate\Support\Js;
    use Illuminate\Support\Str;

    // Auto-reopen modal kalau validation error sebelumnya — baca old('_modal').
    // Format: 'create' atau 'edit:{id}' atau 'detail:{id}'
    $oldModal = old('_modal', request()->query('modal'));
    $initialShowCreate = $oldModal === 'create' ? 'true' : 'false';
    $initialEditId = Str::startsWith($oldModal ?? '', 'edit:') ? (int) Str::after($oldModal, 'edit:') : 'null';
    $initialDetailId = Str::startsWith($oldModal ?? '', 'detail:') ? (int) Str::after($oldModal, 'detail:') : 'null';

    // Status guard: tambah/edit/hapus hanya saat draft (kandidat tidak boleh diubah saat voting jalan)
    $canMutate = $election->status === 'draft';
@endphp

@section('content')
    <div class="flex flex-col gap-xl"
         x-data="candidatesPage({
            initialShowCreate: {{ $initialShowCreate }},
            initialEditId: {{ $initialEditId }},
            initialDetailId: {{ $initialDetailId }},
            search: '',
         })">

        {{-- Header --}}
        <section class="flex flex-col md:flex-row md:items-center md:justify-between gap-md">
            <div class="flex flex-col gap-xs">
                <h1 class="font-h1 text-h1 text-primary">Kelola Kandidat</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $election->name }} — Kelola daftar kandidat untuk pemilihan ini.</p>
            </div>
            @if ($canMutate)
                <button type="button"
                        @click="openCreate()"
                        class="bg-primary-container text-on-primary hover:bg-primary transition-colors font-bold px-6 py-3 rounded-full flex items-center justify-center gap-2 h-[56px] shadow-[0_4px_14px_0_rgba(229,161,0,0.30)] shrink-0">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Kandidat
                </button>
            @else
                <div class="inline-flex items-center gap-2 bg-surface-container-low border border-outline-variant rounded-2xl px-4 py-3 text-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[20px] text-outline">lock</span>
                    Pemilihan {{ $election->status }} — kandidat tidak bisa diubah
                </div>
            @endif
        </section>

        {{-- Action bar (search) --}}
        @if ($candidates->count() > 0)
            <div class="bg-white p-sm rounded-xl border border-outline-variant/30 flex flex-col md:flex-row gap-sm items-center shadow-sm">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input type="text" x-model="search"
                           placeholder="Cari nama kandidat..."
                           class="w-full bg-surface-container-low border-0 focus:ring-2 focus:ring-primary-container rounded-lg py-sm pl-12 pr-4 text-on-background placeholder:text-outline-variant h-12">
                </div>
                <div class="text-sm text-on-surface-variant whitespace-nowrap pr-2">
                    <span x-text="visibleCount" class="font-bold tabular-nums">{{ $candidates->count() }}</span> dari {{ $candidates->count() }} kandidat
                </div>
            </div>
        @endif

        {{-- Grid Cards --}}
        @if ($candidates->isEmpty())
            <div class="bg-white p-xl rounded-2xl border border-outline-variant flex flex-col items-center text-center gap-md">
                <div class="w-16 h-16 rounded-full bg-[#FFFBEA] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#E5A100]" style="font-size: 36px;">groups</span>
                </div>
                <div>
                    <h2 class="font-h2 text-h2 text-on-background mb-1">Belum Ada Kandidat</h2>
                    <p class="text-on-surface-variant">Tambahkan kandidat agar pemilihan bisa dimulai.</p>
                </div>
                @if ($canMutate)
                    <button type="button" @click="openCreate()"
                            class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors">
                        <span class="material-symbols-outlined">add</span>
                        Tambah Kandidat Pertama
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
                @foreach ($candidates as $candidate)
                    @php
                        $num = str_pad($candidate->sort_order ?? $loop->iteration, 2, '0', STR_PAD_LEFT);
                        $excerpt = Str::limit($candidate->visi, 110, '...');
                        // Js::from() = HTML+JS-safe encoding (handles ', ", <, >, & dengan benar)
                        $editPayload = Js::from([
                            'id' => $candidate->id,
                            'name' => $candidate->name,
                            'visi' => $candidate->visi,
                            'misi' => $candidate->misi,
                            'video_url' => $candidate->video_url ?? '',
                            'sort_order' => $candidate->sort_order ?? '',
                            'photo_url' => $candidate->photo_path ? asset('storage/' . $candidate->photo_path) : null,
                        ]);
                        $detailPayload = Js::from([
                            'id' => $candidate->id,
                            'number' => $num,
                            'name' => $candidate->name,
                            'visi' => $candidate->visi,
                            'misi' => $candidate->misi,
                            'video_url' => $candidate->video_url ?? '',
                            'photo_url' => $candidate->photo_path ? asset('storage/' . $candidate->photo_path) : null,
                        ]);
                    @endphp

                    <article class="candidate-card group bg-white rounded-2xl border border-outline-variant/30 overflow-hidden hover:border-primary-container hover:shadow-[0_8px_30px_0_rgba(229,161,0,0.15)] transition-all duration-300 relative flex flex-col"
                             data-name="{{ Str::lower($candidate->name) }}"
                             x-show="matches('{{ Str::lower($candidate->name) }}')">

                        {{-- Action buttons (top-right) --}}
                        @if ($canMutate)
                            <div class="absolute top-3 right-3 bg-white border border-outline-variant/20 rounded-full px-1.5 py-1 flex items-center gap-0.5 shadow-sm z-10">
                                <button type="button"
                                        @click="openEdit({{ $editPayload }})"
                                        title="Edit"
                                        class="text-outline hover:text-primary-container transition-colors p-1.5 rounded-full hover:bg-surface-container-low flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px] leading-none">edit</span>
                                </button>
                                <button type="button"
                                        @click="openDelete({{ Js::from([
                                            'id' => $candidate->id,
                                            'name' => $candidate->name,
                                            'number' => $num,
                                            'photo_url' => $candidate->photo_path ? asset('storage/' . $candidate->photo_path) : null,
                                            'action' => route('admin.elections.candidates.destroy', [$election, $candidate]),
                                        ]) }})"
                                        title="Hapus"
                                        class="text-outline hover:text-error transition-colors p-1.5 rounded-full hover:bg-red-50 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px] leading-none">delete</span>
                                </button>
                            </div>
                        @endif

                        {{-- Body --}}
                        <div class="p-lg flex flex-col items-center relative z-0 pt-10">
                            {{-- Number badge --}}
                            <div class="absolute top-3 left-4 bg-secondary-container text-on-secondary-container font-bold text-lg w-10 h-10 rounded-full flex items-center justify-center shadow-sm">
                                {{ $num }}
                            </div>

                            {{-- Photo --}}
                            <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-surface-container mb-4
                                        @if(!$candidate->photo_path) bg-surface-container-high flex items-center justify-center @endif">
                                @if ($candidate->photo_path)
                                    <img src="{{ asset('storage/' . $candidate->photo_path) }}"
                                         alt="{{ $candidate->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-[40px] text-outline-variant leading-none">person</span>
                                @endif
                            </div>

                            {{-- Name --}}
                            <h3 class="font-bold text-lg text-on-background text-center">{{ $candidate->name }}</h3>

                            {{-- Visi excerpt --}}
                            @if ($candidate->visi)
                                <div class="bg-surface-container-low text-on-surface-variant text-sm text-center p-3 rounded-lg w-full italic mt-3">
                                    "{{ $excerpt }}"
                                </div>
                            @endif
                        </div>

                        {{-- Detail button --}}
                        <div class="mt-auto border-t border-outline-variant/20">
                            <button type="button"
                                    @click="openDetail({{ $detailPayload }})"
                                    class="w-full py-3 text-primary font-label-caps text-label-caps hover:bg-surface-container-low transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px] leading-none">visibility</span>
                                <span>Lihat Detail</span>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Empty state untuk search no result --}}
            <div x-show="visibleCount === 0" x-cloak
                 class="bg-white p-xl rounded-2xl border border-outline-variant text-center">
                <span class="material-symbols-outlined text-outline-variant" style="font-size: 48px;">search_off</span>
                <p class="text-on-surface-variant mt-2">Tidak ada kandidat yang cocok dengan pencarian.</p>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MODAL: CREATE                                                --}}
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
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <h3 class="font-h2 text-h2 text-primary">Tambah Kandidat</h3>
                    <button type="button" @click="showCreate = false"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.elections.candidates.store', $election) }}" method="POST" enctype="multipart/form-data"
                      class="flex flex-col overflow-y-auto"
                      x-data="{ photoPreview: null }">
                    @csrf
                    <input type="hidden" name="_modal" value="create">

                    <div class="p-lg flex flex-col gap-md overflow-y-auto">
                        {{-- Photo --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2">Foto Kandidat</label>
                            <div class="flex items-center gap-md">
                                <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-surface-container bg-surface-container-high flex items-center justify-center shrink-0">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!photoPreview">
                                        <span class="material-symbols-outlined text-[36px] text-outline-variant">add_a_photo</span>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="photo" accept="image/jpg,image/jpeg,image/png"
                                           @change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                           class="block w-full text-sm text-on-surface-variant file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-[#FEF9E7] file:text-[#B45309] hover:file:bg-[#FFFBEA] cursor-pointer">
                                    <p class="mt-1 text-xs text-outline">JPG, JPEG, PNG. Maks 2MB.</p>
                                    @if (old('_modal') === 'create')
                                        @error('photo') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Nama Kandidat <span class="text-error">*</span>
                            </label>
                            <input type="text" name="name"
                                   value="{{ old('_modal') === 'create' ? old('name') : '' }}"
                                   placeholder="Nama lengkap kandidat"
                                   required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (old('_modal') === 'create')
                                @error('name') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Visi --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Visi <span class="text-error">*</span>
                            </label>
                            <textarea name="visi" rows="3" required
                                      placeholder="Tuliskan visi kandidat..."
                                      class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all resize-y">{{ old('_modal') === 'create' ? old('visi') : '' }}</textarea>
                            @if (old('_modal') === 'create')
                                @error('visi') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Misi --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Misi <span class="text-error">*</span>
                            </label>
                            <textarea name="misi" rows="4" required
                                      placeholder="Tuliskan misi kandidat (boleh dipisah dengan baris baru)..."
                                      class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all resize-y">{{ old('_modal') === 'create' ? old('misi') : '' }}</textarea>
                            @if (old('_modal') === 'create')
                                @error('misi') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            {{-- Video URL --}}
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">URL Video</label>
                                <input type="url" name="video_url"
                                       value="{{ old('_modal') === 'create' ? old('video_url') : '' }}"
                                       placeholder="https://youtube.com/..."
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                                @if (old('_modal') === 'create')
                                    @error('video_url') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>

                            {{-- Sort order --}}
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">Nomor Urut</label>
                                <input type="number" name="sort_order" min="1"
                                       value="{{ old('_modal') === 'create' ? old('sort_order') : ($candidates->count() + 1) }}"
                                       placeholder="1"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                                @if (old('_modal') === 'create')
                                    @error('sort_order') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
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
                            Simpan Kandidat
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: EDIT                                                  --}}
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
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <h3 class="font-h2 text-h2 text-primary">Edit Kandidat</h3>
                    <button type="button" @click="editingId = null"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="`{{ url('admin/elections/' . $election->id . '/candidates') }}/${editingId}`" method="POST" enctype="multipart/form-data"
                      class="flex flex-col overflow-y-auto"
                      x-data="{ newPhotoPreview: null }">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" :value="`edit:${editingId}`">

                    <div class="p-lg flex flex-col gap-md overflow-y-auto">
                        {{-- Photo --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2">Foto Kandidat</label>
                            <div class="flex items-center gap-md">
                                <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-surface-container bg-surface-container-high flex items-center justify-center shrink-0">
                                    <template x-if="newPhotoPreview">
                                        <img :src="newPhotoPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!newPhotoPreview && editForm.photo_url">
                                        <img :src="editForm.photo_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!newPhotoPreview && !editForm.photo_url">
                                        <span class="material-symbols-outlined text-[36px] text-outline-variant">person</span>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="photo" accept="image/jpg,image/jpeg,image/png"
                                           @change="newPhotoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                           class="block w-full text-sm text-on-surface-variant file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-[#FEF9E7] file:text-[#B45309] hover:file:bg-[#FFFBEA] cursor-pointer">
                                    <p class="mt-1 text-xs text-outline">Kosongkan untuk tetap pakai foto lama. JPG/JPEG/PNG, maks 2MB.</p>
                                    @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                        @error('photo') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Nama Kandidat <span class="text-error">*</span>
                            </label>
                            <input type="text" name="name" x-model="editForm.name" required
                                   class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('name') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Visi --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Visi <span class="text-error">*</span>
                            </label>
                            <textarea name="visi" rows="3" x-model="editForm.visi" required
                                      class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all resize-y"></textarea>
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('visi') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Misi --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1">
                                Misi <span class="text-error">*</span>
                            </label>
                            <textarea name="misi" rows="4" x-model="editForm.misi" required
                                      class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all resize-y"></textarea>
                            @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                @error('misi') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">URL Video</label>
                                <input type="url" name="video_url" x-model="editForm.video_url"
                                       placeholder="https://youtube.com/..."
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                                @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                    @error('video_url') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-on-surface mb-1">Nomor Urut</label>
                                <input type="number" name="sort_order" x-model="editForm.sort_order" min="1"
                                       class="w-full px-4 py-3 border border-outline-variant rounded-xl text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-4 focus:ring-primary-container/20 transition-all">
                                @if (Str::startsWith(old('_modal') ?? '', 'edit:'))
                                    @error('sort_order') <p class="mt-1 text-sm text-error font-medium">{{ $message }}</p> @enderror
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
        {{-- MODAL: DETAIL (read-only)                                    --}}
        {{-- ============================================================ --}}
        <div x-show="detailingId !== null" x-transition.opacity x-cloak
             @keydown.escape.window="detailingId = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="detailingId = null"></div>
            <div x-show="detailingId !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="bg-secondary-container text-on-secondary-container font-bold w-10 h-10 rounded-full flex items-center justify-center tabular-nums" x-text="detailData.number"></div>
                        <h3 class="font-h2 text-h2 text-on-background" x-text="detailData.name"></h3>
                    </div>
                    <button type="button" @click="detailingId = null"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-lg flex flex-col gap-lg overflow-y-auto">
                    {{-- Foto --}}
                    <div class="flex justify-center">
                        <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-surface-container bg-surface-container-high flex items-center justify-center">
                            <template x-if="detailData.photo_url">
                                <img :src="detailData.photo_url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!detailData.photo_url">
                                <span class="material-symbols-outlined text-[64px] text-outline-variant">person</span>
                            </template>
                        </div>
                    </div>

                    {{-- Visi --}}
                    <div>
                        <h4 class="font-bold text-sm uppercase tracking-wider text-primary mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">flag</span>
                            Visi
                        </h4>
                        <p class="text-on-surface italic bg-surface-container-low p-md rounded-xl whitespace-pre-line" x-text="detailData.visi"></p>
                    </div>

                    {{-- Misi --}}
                    <div>
                        <h4 class="font-bold text-sm uppercase tracking-wider text-primary mb-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">checklist</span>
                            Misi
                        </h4>
                        <p class="text-on-surface bg-surface-container-low p-md rounded-xl whitespace-pre-line" x-text="detailData.misi"></p>
                    </div>

                    {{-- Video link --}}
                    <template x-if="detailData.video_url">
                        <div>
                            <h4 class="font-bold text-sm uppercase tracking-wider text-primary mb-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">smart_display</span>
                                Video Profil
                            </h4>
                            <a :href="detailData.video_url" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-[#FEF9E7] border border-[#FDE68A] rounded-full text-[#B45309] font-bold text-sm hover:bg-[#FFFBEA] transition-colors">
                                <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                <span x-text="detailData.video_url" class="truncate max-w-xs"></span>
                            </a>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant shrink-0">
                    @if ($canMutate)
                        <button type="button" @click="openEditFromDetail()"
                                class="inline-flex items-center gap-sm bg-primary-container text-on-primary px-5 py-3 rounded-full font-bold hover:bg-primary transition-colors">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                            Edit Kandidat
                        </button>
                    @endif
                    <button type="button" @click="detailingId = null"
                            class="px-5 py-3 rounded-full font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: DELETE CONFIRM                                        --}}
        {{-- ============================================================ --}}
        <div x-show="deletingCandidate !== null" x-transition.opacity x-cloak
             @keydown.escape.window="deletingCandidate = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="deletingCandidate = null"></div>
            <div x-show="deletingCandidate !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">

                {{-- Header dengan icon warning --}}
                <div class="px-lg pt-lg pb-md flex flex-col items-center text-center gap-sm">
                    <div class="w-16 h-16 rounded-full bg-error-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-error" style="font-size: 32px;">warning</span>
                    </div>
                    <h3 class="font-h2 text-h2 text-on-background">Hapus Kandidat?</h3>
                    <p class="text-on-surface-variant text-sm">Tindakan ini tidak bisa dibatalkan.</p>
                </div>

                {{-- Identity card kandidat yang akan dihapus --}}
                <div class="mx-lg my-md p-md bg-surface-container-low border border-outline-variant rounded-2xl flex items-center gap-md">
                    <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-surface-container bg-surface-container-high flex items-center justify-center shrink-0">
                        <template x-if="deletingCandidate?.photo_url">
                            <img :src="deletingCandidate?.photo_url" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!deletingCandidate?.photo_url">
                            <span class="material-symbols-outlined text-outline-variant" style="font-size: 28px;">person</span>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="bg-secondary-container text-on-secondary-container text-xs font-bold px-2 py-0.5 rounded-full tabular-nums" x-text="deletingCandidate?.number"></span>
                        </div>
                        <p class="font-bold text-on-background truncate mt-1" x-text="deletingCandidate?.name"></p>
                    </div>
                </div>

                {{-- Hidden form untuk submit DELETE --}}
                <form x-ref="deleteForm" :action="deletingCandidate?.action" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                {{-- Actions --}}
                <div class="flex items-center gap-md p-lg bg-surface-container-low border-t border-outline-variant">
                    <button type="button" @click="deletingCandidate = null"
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
    </div>

    @push('scripts')
    <script>
        function candidatesPage(initial) {
            return {
                showCreate: initial.initialShowCreate,
                editingId: initial.initialEditId,
                detailingId: initial.initialDetailId,
                deletingCandidate: null,
                search: initial.search,
                visibleCount: {{ $candidates->count() }},

                // Edit modal form state — populated by openEdit()
                editForm: {
                    name: @json(old('name', '')),
                    visi: @json(old('visi', '')),
                    misi: @json(old('misi', '')),
                    video_url: @json(old('video_url', '')),
                    sort_order: @json(old('sort_order', '')),
                    photo_url: null,
                },

                // Detail modal data
                detailData: { number: '', name: '', visi: '', misi: '', video_url: '', photo_url: null },

                openCreate() {
                    this.editingId = null;
                    this.detailingId = null;
                    this.showCreate = true;
                },

                openEdit(candidate) {
                    this.showCreate = false;
                    this.detailingId = null;
                    this.editingId = candidate.id;
                    @if (!Str::startsWith(old('_modal') ?? '', 'edit:'))
                    this.editForm = {
                        name: candidate.name,
                        visi: candidate.visi,
                        misi: candidate.misi,
                        video_url: candidate.video_url || '',
                        sort_order: candidate.sort_order || '',
                        photo_url: candidate.photo_url,
                    };
                    @endif
                },

                openDetail(candidate) {
                    this.detailData = candidate;
                    this.detailingId = candidate.id;
                },

                openDelete(candidate) {
                    // Tutup modal lain biar tidak overlap
                    this.showCreate = false;
                    this.editingId = null;
                    this.detailingId = null;
                    this.deletingCandidate = candidate;
                },

                openEditFromDetail() {
                    // dari detail → edit pakai data yang sama
                    const c = this.detailData;
                    this.openEdit({
                        id: c.id,
                        name: c.name,
                        visi: c.visi,
                        misi: c.misi,
                        video_url: c.video_url,
                        sort_order: '',
                        photo_url: c.photo_url,
                    });
                },

                // Search filter (client-side)
                matches(name) {
                    if (!this.search.trim()) return true;
                    return name.includes(this.search.toLowerCase().trim());
                },

                init() {
                    // Recompute visibleCount when search changes
                    this.$watch('search', () => {
                        this.$nextTick(() => {
                            this.visibleCount = document.querySelectorAll('.candidate-card:not([style*="display: none"])').length;
                        });
                    });
                },
            };
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @endpush
@endsection
