@extends('layouts.arutala-admin')

@section('title', 'Hasil Import DPT')
@section('breadcrumb', 'Hasil Import DPT')

@section('content')
    <div class="flex flex-col gap-xl">
        {{-- Header --}}
        <section class="flex flex-col gap-xs">
            <div class="flex items-center gap-sm text-sm text-on-surface-variant">
                <a href="{{ route('admin.dpt.index', $election) }}" class="hover:text-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke Daftar DPT
                </a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="font-medium text-on-surface truncate">{{ $election->name }}</span>
            </div>
            <h1 class="font-h1 text-h1 text-on-surface">Hasil Import DPT</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Ringkasan proses import file Excel.</p>
        </section>

        {{-- Summary cards --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-md">
            {{-- Sukses --}}
            <div class="bg-white border border-[#A7F3D0] rounded-2xl p-lg flex items-center gap-md glow-shadow relative overflow-hidden">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-[#ECFDF5] rounded-full blur-2xl pointer-events-none"></div>
                <div class="w-14 h-14 rounded-full bg-[#ECFDF5] flex items-center justify-center text-[#059669] shrink-0 z-10">
                    <span class="material-symbols-outlined fill" style="font-size: 32px;">check_circle</span>
                </div>
                <div class="z-10">
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Berhasil Diimport</p>
                    <p class="font-display text-display-mobile text-[#059669] tabular-nums leading-none mt-1">
                        {{ number_format($result['success_count']) }}
                    </p>
                </div>
            </div>

            {{-- Gagal --}}
            <div class="bg-white border @if ($result['failure_count'] > 0) border-[#FECACA] @else border-outline-variant @endif rounded-2xl p-lg flex items-center gap-md glow-shadow relative overflow-hidden">
                @if ($result['failure_count'] > 0)
                    <div class="absolute -top-8 -right-8 w-32 h-32 bg-[#FEF2F2] rounded-full blur-2xl pointer-events-none"></div>
                @endif
                <div class="w-14 h-14 rounded-full @if ($result['failure_count'] > 0) bg-[#FEF2F2] text-[#DC2626] @else bg-surface-container-high text-outline @endif flex items-center justify-center shrink-0 z-10">
                    <span class="material-symbols-outlined" style="font-size: 32px;">{{ $result['failure_count'] > 0 ? 'error' : 'check' }}</span>
                </div>
                <div class="z-10">
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Gagal</p>
                    <p class="font-display text-display-mobile @if ($result['failure_count'] > 0) text-[#DC2626] @else text-on-surface @endif tabular-nums leading-none mt-1">
                        {{ number_format($result['failure_count']) }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Detail error table --}}
        @if ($result['failure_count'] > 0)
            <section class="bg-white border border-outline-variant rounded-2xl overflow-hidden glow-shadow">
                <div class="p-md border-b border-outline-variant bg-surface-container-low flex items-center gap-2">
                    <span class="material-symbols-outlined text-error">report</span>
                    <h3 class="font-h2 text-h2 text-on-surface">Detail Error</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container text-on-surface-variant font-label-caps text-label-caps uppercase">
                                <th class="p-4 border-b border-outline-variant">Baris</th>
                                <th class="p-4 border-b border-outline-variant">Kolom</th>
                                <th class="p-4 border-b border-outline-variant">Error</th>
                                <th class="p-4 border-b border-outline-variant">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach ($result['errors'] as $error)
                                <tr class="hover:bg-[#FEF2F2]/30 transition-colors">
                                    <td class="p-4 font-mono font-bold text-on-surface text-sm">#{{ $error['row'] }}</td>
                                    <td class="p-4">
                                        <span class="inline-block px-2 py-0.5 bg-surface-container-high text-on-surface-variant rounded text-xs font-bold">
                                            {{ $error['attribute'] }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        @foreach ($error['errors'] as $message)
                                            <div class="text-sm text-error font-medium flex items-start gap-1">
                                                <span class="material-symbols-outlined text-[16px] mt-0.5 shrink-0">error</span>
                                                <span>{{ $message }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="p-4">
                                        <span class="font-mono text-sm text-on-surface-variant bg-surface-container-low px-2 py-1 rounded">
                                            {{ $error['values'][$error['attribute']] ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            {{-- All good banner --}}
            <section class="bg-[#ECFDF5] border border-[#A7F3D0] rounded-2xl p-lg flex items-center gap-md">
                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-[#059669] shrink-0">
                    <span class="material-symbols-outlined fill" style="font-size: 28px;">verified</span>
                </div>
                <div>
                    <h3 class="font-bold text-[#065F46] text-lg">Semua data berhasil diimport!</h3>
                    <p class="text-sm text-[#047857]">Tidak ada error pada file yang Anda upload.</p>
                </div>
            </section>
        @endif

        {{-- Actions --}}
        <section class="flex flex-wrap gap-md">
            <a href="{{ route('admin.dpt.index', $election) }}"
               class="inline-flex items-center gap-sm bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors shadow-md">
                <span class="material-symbols-outlined">groups</span>
                Lihat Daftar DPT
            </a>
            <a href="{{ route('admin.dpt.index', $election, ['modal' => 'import']) }}"
               class="inline-flex items-center gap-sm bg-white border border-outline-variant text-on-surface px-6 py-3 rounded-full font-bold hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined">upload_file</span>
                Import Lagi
            </a>
        </section>
    </div>
@endsection
