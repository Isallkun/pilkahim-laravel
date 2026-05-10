@extends('layouts.arutala-admin')

@section('title', 'Laporan & Export')
@section('breadcrumb', 'Laporan')

@php
    use Illuminate\Support\Str;

    // Mapping action audit log → metadata report (untuk Export History).
    $reportMeta = [
        'generate_attendance_report' => [
            'label' => 'Daftar Hadir',
            'icon' => 'checklist',
            'extension' => 'pdf',
            'route' => 'admin.reports.attendance',
        ],
        'generate_result_report' => [
            'label' => 'Hasil Pemilihan',
            'icon' => 'bar_chart',
            'extension' => 'pdf',
            'route' => 'admin.reports.result',
        ],
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-xl">
        {{-- Header --}}
        <section class="flex flex-col md:flex-row md:items-end md:justify-between gap-md">
            <div class="flex flex-col gap-xs">
                <h1 class="font-h1 text-h1 text-primary">Laporan &amp; Export</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Generate, lihat, dan unduh laporan pemilihan dengan aman.</p>
            </div>

            {{-- Election selector kalau ada lebih dari 1 --}}
            @if ($elections->count() > 1)
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 bg-white border border-outline-variant rounded-2xl px-4 py-2 shadow-sm m-0">
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
            {{-- Empty state — belum ada election --}}
            <section class="bg-white p-xl rounded-2xl border border-outline-variant flex flex-col items-center text-center gap-md">
                <div class="w-16 h-16 rounded-full bg-[#FFFBEA] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#E5A100]" style="font-size: 36px;">summarize</span>
                </div>
                <div>
                    <h2 class="font-h2 text-h2 text-on-background mb-1">Belum Ada Pemilihan</h2>
                    <p class="text-on-surface-variant">Buat event pemilihan dulu sebelum bisa generate laporan.</p>
                </div>
                <a href="{{ route('admin.elections.index') }}"
                   class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors">
                    <span class="material-symbols-outlined">add</span>
                    Buat Event Pemilihan
                </a>
            </section>
        @else
            {{-- Active election context indicator --}}
            <section class="bg-white border border-outline-variant rounded-2xl p-md flex items-center gap-md">
                <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0">
                    <span class="material-symbols-outlined leading-none">how_to_vote</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Konteks Laporan</p>
                    <p class="font-bold text-on-surface truncate">{{ $selectedElection->name }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @php
                        $statusBadge = match($selectedElection->status) {
                            'active' => ['bg-[#FFFBEA]', 'text-[#D97706]', 'border-[#FDE68A]', 'Aktif'],
                            'finished', 'completed' => ['bg-[#ECFDF5]', 'text-[#059669]', 'border-[#A7F3D0]', 'Selesai'],
                            default => ['bg-surface-container', 'text-on-surface-variant', 'border-outline-variant', ucfirst($selectedElection->status)],
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full {{ $statusBadge[0] }} {{ $statusBadge[1] }} border {{ $statusBadge[2] }} text-xs font-bold uppercase tracking-wider">
                        {{ $statusBadge[3] }}
                    </span>
                </div>
            </section>

            {{-- Bento Grid: Report Categories --}}
            <section class="grid grid-cols-1 md:grid-cols-3 gap-md">
                {{-- Daftar Hadir --}}
                <div class="bg-white border border-outline-variant rounded-2xl p-lg flex flex-col gap-md hover:border-primary-container hover:shadow-[0_8px_30px_0_rgba(229,161,0,0.12)] transition-all group">
                    <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-primary group-hover:bg-primary-container group-hover:text-on-primary transition-colors">
                        <span class="material-symbols-outlined">checklist</span>
                    </div>
                    <div>
                        <h3 class="font-h2 text-h2 text-on-surface">Daftar Hadir</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-sm">Daftar pemilih dan timestamp partisipasi voting.</p>
                    </div>
                    <div class="mt-auto pt-md flex gap-sm">
                        <a href="{{ route('admin.reports.attendance', $selectedElection) }}"
                           target="_blank" rel="noopener"
                           class="flex-1 h-12 rounded-full bg-primary-container text-on-primary font-bold hover:bg-[#D97706] transition-colors flex items-center justify-center gap-1 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> PDF
                        </a>
                        <button type="button" disabled
                                title="Coming Soon — belum tersedia"
                                class="flex-1 h-12 rounded-full bg-surface-container-low text-outline font-bold cursor-not-allowed flex items-center justify-center gap-1 opacity-60">
                            <span class="material-symbols-outlined text-[18px]">table_view</span> Excel
                        </button>
                    </div>
                </div>

                {{-- Hasil Pemilihan / Statistik --}}
                <div class="bg-white border border-outline-variant rounded-2xl p-lg flex flex-col gap-md hover:border-primary-container hover:shadow-[0_8px_30px_0_rgba(229,161,0,0.12)] transition-all group">
                    <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-primary group-hover:bg-primary-container group-hover:text-on-primary transition-colors">
                        <span class="material-symbols-outlined">bar_chart</span>
                    </div>
                    <div>
                        <h3 class="font-h2 text-h2 text-on-surface">Hasil &amp; Statistik</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-sm">Ringkasan suara per kandidat plus metrik turnout.</p>
                    </div>
                    <div class="mt-auto pt-md flex gap-sm">
                        <a href="{{ route('admin.reports.result', $selectedElection) }}"
                           target="_blank" rel="noopener"
                           class="flex-1 h-12 rounded-full bg-primary-container text-on-primary font-bold hover:bg-[#D97706] transition-colors flex items-center justify-center gap-1 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> PDF
                        </a>
                        <button type="button" disabled
                                title="Coming Soon — belum tersedia"
                                class="flex-1 h-12 rounded-full bg-surface-container-low text-outline font-bold cursor-not-allowed flex items-center justify-center gap-1 opacity-60">
                            <span class="material-symbols-outlined text-[18px]">image</span> PNG
                        </button>
                    </div>
                </div>

                {{-- Berita Acara — Coming Soon --}}
                <div class="bg-surface-container-low border border-dashed border-outline-variant rounded-2xl p-lg flex flex-col gap-md relative overflow-hidden">
                    <div class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 bg-[#FEF3C7] text-[#A16207] border border-[#FEF08A] rounded text-[10px] font-bold uppercase tracking-wider">
                        Coming Soon
                    </div>
                    <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center text-outline">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <div>
                        <h3 class="font-h2 text-h2 text-on-surface-variant">Berita Acara</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-sm">Dokumen resmi prosedur pemilihan dan catatan anomali.</p>
                    </div>
                    <div class="mt-auto pt-md flex gap-sm">
                        <button type="button" disabled
                                class="flex-1 h-12 rounded-full bg-surface-container-low text-outline font-bold cursor-not-allowed flex items-center justify-center gap-1 opacity-60">
                            <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> PDF
                        </button>
                        <button type="button" disabled
                                class="flex-1 h-12 rounded-full bg-surface-container-low text-outline font-bold cursor-not-allowed flex items-center justify-center gap-1 opacity-60">
                            <span class="material-symbols-outlined text-[18px]">description</span> DOCX
                        </button>
                    </div>
                </div>
            </section>

            {{-- Lower Section: Live Presentation + Export History --}}
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-md">
                {{-- Live Presentation --}}
                <div class="lg:col-span-1 bg-primary-fixed rounded-2xl p-lg flex flex-col justify-between border border-primary-fixed-dim relative overflow-hidden min-h-[280px]">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary-container/20 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col gap-md">
                        <div class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-primary shadow-sm">
                            <span class="material-symbols-outlined" style="font-size: 32px;">present_to_all</span>
                        </div>
                        <div>
                            <h3 class="font-h2 text-h2 text-on-primary-container">Live Presentation</h3>
                            <p class="font-body-md text-body-md text-on-primary-container/80 mt-sm">
                                Layar publik real-time perhitungan suara — cocok untuk projector di acara closing.
                            </p>
                        </div>
                    </div>

                    @if ($publicResultElection)
                        <a href="{{ route('results.public', $publicResultElection) }}"
                           target="_blank" rel="noopener"
                           class="mt-md w-full h-[56px] rounded-full bg-primary-container text-on-primary font-bold text-lg hover:bg-[#D97706] transition-colors flex items-center justify-center gap-sm relative z-10 shadow-[0_4px_14px_0_rgba(229,161,0,0.40)]">
                            <span class="material-symbols-outlined">play_arrow</span>
                            Launch Mode
                        </a>
                    @else
                        <div class="mt-md w-full relative z-10">
                            <button type="button" disabled
                                    class="w-full h-[56px] rounded-full bg-white/50 text-on-primary-container/60 font-bold text-base cursor-not-allowed flex items-center justify-center gap-sm">
                                <span class="material-symbols-outlined">visibility_off</span>
                                Hasil Belum Public
                            </button>
                            <p class="text-xs text-on-primary-container/70 mt-2 text-center">
                                Aktifkan visibility hasil di
                                <a href="{{ route('admin.elections.index') }}" class="font-bold underline hover:text-on-primary-container">Event Pemilihan</a>
                                supaya layar live bisa di-launch.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Export History --}}
                <div class="lg:col-span-2 bg-white border border-outline-variant rounded-2xl p-lg glow-shadow flex flex-col">
                    <div class="flex justify-between items-center mb-md pb-sm border-b border-outline-variant">
                        <h3 class="font-h2 text-h2 text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">history</span>
                            Riwayat Export
                        </h3>
                        <a href="{{ route('admin.audit-logs.index', ['action' => 'generate_attendance_report']) }}"
                           class="text-primary font-bold text-sm hover:text-primary-container flex items-center gap-1">
                            Lihat di Audit Log <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>

                    @if ($exportHistory->count() > 0)
                        <div class="flex flex-col">
                            @foreach ($exportHistory as $export)
                                @php
                                    $meta = $reportMeta[$export->action] ?? ['label' => 'Report', 'icon' => 'description', 'extension' => 'pdf', 'route' => null];
                                    $electionId = $export->details['election_id'] ?? null;
                                    $electionName = $export->details['election_name'] ?? '—';
                                    $filename = Str::slug($meta['label'], '_') . '_' . Str::slug($electionName, '_') . '.' . $meta['extension'];
                                @endphp
                                <div class="flex items-center justify-between py-3 border-b border-outline-variant/40 last:border-0 hover:bg-surface-container-low transition-colors rounded-lg px-2 -mx-2">
                                    <div class="flex items-center gap-md min-w-0">
                                        <div class="w-10 h-10 rounded-full bg-[#FFFBEA] text-[#D97706] flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">{{ $meta['icon'] }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-on-surface truncate font-mono text-sm">{{ $filename }}</h4>
                                            <p class="text-xs text-on-surface-variant">
                                                <span class="tabular-nums">{{ $export->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                                                <span class="text-outline">·</span>
                                                {{ $export->user?->name ?? 'Sistem' }}
                                                <span class="text-outline">·</span>
                                                {{ $export->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    @if ($meta['route'] && $electionId)
                                        <a href="{{ route($meta['route'], $electionId) }}" target="_blank" rel="noopener"
                                           title="Generate ulang & download"
                                           class="w-10 h-10 rounded-full hover:bg-surface-container text-on-surface-variant hover:text-primary flex items-center justify-center transition-colors shrink-0 ml-2">
                                            <span class="material-symbols-outlined">download</span>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center text-center gap-sm py-md">
                            <span class="material-symbols-outlined text-outline-variant" style="font-size: 48px;">inbox</span>
                            <p class="text-on-surface-variant text-sm">Belum ada riwayat export.</p>
                            <p class="text-xs text-outline">Klik tombol PDF di atas untuk generate laporan pertama.</p>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>
@endsection
