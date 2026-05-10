@extends('layouts.arutala-admin')

@section('title', 'Audit Log')
@section('breadcrumb', 'Audit Log')

@php
    use Illuminate\Support\Js;

    // Mapping action → label, warna badge, kategori untuk filter scope.
    // Kategori: create=hijau, update=biru, delete/destroy=merah, import/restore=orange, reset=kuning
    //           auth=indigo (login/logout), vote=gold (partisipasi pemilih)
    $actionMap = [
        // Auth (semua role)
        'login_success' => ['label' => 'Login Berhasil', 'color' => 'indigo', 'scope' => 'auth'],
        'login_failed' => ['label' => 'Login Gagal', 'color' => 'red', 'scope' => 'auth'],
        'logout' => ['label' => 'Logout', 'color' => 'gray', 'scope' => 'auth'],

        // Voter activity
        'submit_vote' => ['label' => 'Submit Suara', 'color' => 'gold', 'scope' => 'voter'],
        'view_already_voted' => ['label' => 'Buka Halaman (Sudah Vote)', 'color' => 'indigo', 'scope' => 'voter'],
        'duplicate_vote_attempt' => ['label' => 'Coba Vote Ulang', 'color' => 'red', 'scope' => 'voter'],

        // Admin: Election
        'create_election' => ['label' => 'Buat Pemilihan', 'color' => 'green', 'scope' => 'admin'],
        'update_election' => ['label' => 'Update Pemilihan', 'color' => 'blue', 'scope' => 'admin'],
        'toggle_results' => ['label' => 'Toggle Hasil Public', 'color' => 'blue', 'scope' => 'admin'],

        // Admin: Settings
        'toggle_countdown' => ['label' => 'Toggle Countdown', 'color' => 'blue', 'scope' => 'admin'],
        'toggle_result_visibility' => ['label' => 'Toggle Live Count', 'color' => 'blue', 'scope' => 'admin'],
        'close_voting' => ['label' => 'Tutup Voting', 'color' => 'orange', 'scope' => 'admin'],
        'reset_all_votes' => ['label' => 'Reset Semua Suara', 'color' => 'red', 'scope' => 'admin'],

        // Admin: Candidate
        'create_candidate' => ['label' => 'Tambah Kandidat', 'color' => 'green', 'scope' => 'admin'],
        'update_candidate' => ['label' => 'Update Kandidat', 'color' => 'blue', 'scope' => 'admin'],
        'delete_candidate' => ['label' => 'Hapus Kandidat', 'color' => 'red', 'scope' => 'admin'],

        // Admin: Voter / DPT
        'create_voter' => ['label' => 'Tambah Pemilih', 'color' => 'green', 'scope' => 'admin'],
        'update_voter' => ['label' => 'Update Pemilih', 'color' => 'blue', 'scope' => 'admin'],
        'remove_voter' => ['label' => 'Hapus Pemilih', 'color' => 'red', 'scope' => 'admin'],
        'destroy_all_voters' => ['label' => 'Hapus Semua Pemilih', 'color' => 'red', 'scope' => 'admin'],
        'restore_orphan_voters' => ['label' => 'Pulihkan Orphan', 'color' => 'orange', 'scope' => 'admin'],
        'import_dpt' => ['label' => 'Import DPT', 'color' => 'orange', 'scope' => 'admin'],
        'reset_user' => ['label' => 'Reset Pemilih', 'color' => 'yellow', 'scope' => 'admin'],
    ];

    $colorClasses = [
        'green' => 'bg-[#ECFDF5] text-[#059669] border-[#A7F3D0]',
        'blue' => 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]',
        'red' => 'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]',
        'orange' => 'bg-[#FFFBEA] text-[#D97706] border-[#FDE68A]',
        'yellow' => 'bg-[#FEFCE8] text-[#A16207] border-[#FEF08A]',
        'indigo' => 'bg-[#EEF2FF] text-[#4F46E5] border-[#C7D2FE]',
        'gold' => 'bg-[#FFFBEA] text-[#B45309] border-[#FCD34D]',
        'gray' => 'bg-surface-container text-on-surface-variant border-outline-variant',
    ];

    // Stats overview (independent of filter — total semua waktu)
    $totalLogs = \App\Models\AuditLog::count();
    $uniqueActions = \App\Models\AuditLog::distinct('action')->count('action');
    $todayLogs = \App\Models\AuditLog::whereDate('created_at', today())->count();
    $failedLoginToday = \App\Models\AuditLog::where('action', 'login_failed')->whereDate('created_at', today())->count();

    $hasFilter = request()->filled('date_from') || request()->filled('date_to') || request()->filled('action') || request()->filled('role');

    // Role badge style (warna konsisten dengan auth events)
    $roleBadge = [
        'panitia' => 'bg-[#FFFBEA] text-[#B45309] border-[#FCD34D]',
        'pemilih' => 'bg-[#EEF2FF] text-[#4F46E5] border-[#C7D2FE]',
        'saksi'   => 'bg-[#F0FDF4] text-[#16A34A] border-[#BBF7D0]',
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-xl"
         x-data="auditLogPage()">

        {{-- Header --}}
        <section class="flex flex-col md:flex-row md:items-end md:justify-between gap-md">
            <div class="flex flex-col gap-xs">
                <h1 class="font-h1 text-h1 text-primary">Audit Log</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Riwayat aktivitas admin pada sistem.</p>
            </div>
        </section>

        {{-- Stats Strip --}}
        <section class="grid grid-cols-2 md:grid-cols-4 gap-md">
            <div class="bg-white border border-outline-variant rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0">
                    <span class="material-symbols-outlined leading-none">history</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Total</p>
                    <p class="font-h2 text-h2 text-on-surface tabular-nums">{{ number_format($totalLogs) }}</p>
                </div>
            </div>

            <div class="bg-white border border-outline-variant rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full bg-[#EFF6FF] flex items-center justify-center text-[#2563EB] shrink-0">
                    <span class="material-symbols-outlined leading-none">category</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Jenis Aksi</p>
                    <p class="font-h2 text-h2 text-on-surface tabular-nums">{{ number_format($uniqueActions) }}</p>
                </div>
            </div>

            <div class="bg-white border border-outline-variant rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full bg-[#ECFDF5] flex items-center justify-center text-[#059669] shrink-0">
                    <span class="material-symbols-outlined leading-none">today</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Hari Ini</p>
                    <p class="font-h2 text-h2 text-on-surface tabular-nums">{{ number_format($todayLogs) }}</p>
                </div>
            </div>

            <div class="bg-white border @if($failedLoginToday > 0) border-[#FECACA] @else border-outline-variant @endif rounded-2xl p-lg flex items-center gap-md glow-shadow">
                <div class="w-12 h-12 rounded-full @if($failedLoginToday > 0) bg-[#FEF2F2] text-[#DC2626] @else bg-surface-container-high text-outline @endif flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined leading-none">{{ $failedLoginToday > 0 ? 'gpp_bad' : 'gpp_good' }}</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-on-surface-variant uppercase">Login Gagal</p>
                    <p class="font-h2 text-h2 @if($failedLoginToday > 0) text-error @else text-on-surface @endif tabular-nums">{{ number_format($failedLoginToday) }}</p>
                </div>
            </div>
        </section>

        {{-- Filter Card --}}
        <section class="bg-white border border-outline-variant rounded-2xl p-md glow-shadow">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-md items-end">
                <div class="md:col-span-3">
                    <label for="date_from" class="block text-sm font-bold text-on-surface mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-outline-variant rounded-lg text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-2 focus:ring-primary-container/20 h-11">
                </div>
                <div class="md:col-span-3">
                    <label for="date_to" class="block text-sm font-bold text-on-surface mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-outline-variant rounded-lg text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-2 focus:ring-primary-container/20 h-11">
                </div>
                <div class="md:col-span-2">
                    <label for="role" class="block text-sm font-bold text-on-surface mb-1">Role</label>
                    <select name="role" id="role"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-2 focus:ring-primary-container/20 h-11">
                        <option value="">Semua</option>
                        <option value="panitia" @selected(request('role') === 'panitia')>Panitia</option>
                        <option value="pemilih" @selected(request('role') === 'pemilih')>Pemilih</option>
                        <option value="saksi" @selected(request('role') === 'saksi')>Saksi</option>
                        <option value="anonymous" @selected(request('role') === 'anonymous')>Anonymous (gagal login)</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="action" class="block text-sm font-bold text-on-surface mb-1">Jenis Aksi</label>
                    <select name="action" id="action"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-on-surface bg-white focus:outline-none focus:border-primary-container focus:ring-2 focus:ring-primary-container/20 h-11">
                        <option value="">Semua</option>
                        @foreach ($actions as $act)
                            <option value="{{ $act }}" @selected(request('action') === $act)>
                                {{ $actionMap[$act]['label'] ?? $act }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex items-stretch gap-1">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-1 bg-[#E5A100] text-white px-4 py-2 rounded-lg font-bold hover:bg-[#D97706] transition-colors h-11">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                        Filter
                    </button>
                    @if ($hasFilter)
                        <a href="{{ route('admin.audit-logs.index') }}"
                           title="Reset filter"
                           class="inline-flex items-center justify-center px-3 py-2 border border-outline-variant rounded-lg text-outline hover:bg-surface-container-low h-11">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Table Card --}}
        <section class="bg-white border border-outline-variant rounded-2xl overflow-hidden glow-shadow">
            <div class="p-md border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                <h3 class="font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list_alt</span>
                    Riwayat
                </h3>
                <span class="text-sm text-on-surface-variant tabular-nums">
                    @if ($hasFilter)
                        <span class="font-bold">{{ $logs->total() }}</span> hasil filter
                    @else
                        <span class="font-bold">{{ $logs->total() }}</span> entry total
                    @endif
                </span>
            </div>

            @if ($logs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container text-on-surface-variant font-label-caps text-label-caps uppercase">
                                <th class="p-4 border-b border-outline-variant whitespace-nowrap">Waktu</th>
                                <th class="p-4 border-b border-outline-variant">User</th>
                                <th class="p-4 border-b border-outline-variant">Aksi</th>
                                <th class="p-4 border-b border-outline-variant hidden md:table-cell">IP</th>
                                <th class="p-4 border-b border-outline-variant text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="text-on-surface divide-y divide-outline-variant/40">
                            @foreach ($logs as $log)
                                @php
                                    $meta = $actionMap[$log->action] ?? ['label' => str_replace('_', ' ', ucfirst($log->action)), 'color' => 'gray'];
                                    $badgeClass = $colorClasses[$meta['color']];
                                @endphp
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="p-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-on-surface">
                                            {{ $log->created_at->locale('id')->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-xs text-on-surface-variant tabular-nums">
                                            {{ $log->created_at->format('H:i:s') }}
                                            <span class="text-outline">·</span>
                                            {{ $log->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        @if ($log->user)
                                            @php $roleName = $log->user->getRoleNames()->first(); @endphp
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span class="font-medium text-on-surface">{{ $log->user->name }}</span>
                                                @if ($roleName && isset($roleBadge[$roleName]))
                                                    <span class="inline-flex items-center px-1.5 py-0 rounded border {{ $roleBadge[$roleName] }} text-[10px] font-bold uppercase tracking-wider">
                                                        {{ $roleName }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs font-mono text-primary">{{ $log->user->username }}</div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-on-surface-variant italic">Anonymous</span>
                                                <span class="inline-flex items-center px-1.5 py-0 rounded border bg-surface-container-high text-on-surface-variant border-outline-variant text-[10px] font-bold uppercase tracking-wider">
                                                    guest
                                                </span>
                                            </div>
                                            @if (!empty($log->details['attempted_username']))
                                                <div class="text-xs font-mono text-error">attempted: {{ $log->details['attempted_username'] }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full border {{ $badgeClass }} text-xs font-bold uppercase tracking-wider whitespace-nowrap">
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-on-surface-variant text-sm font-mono hidden md:table-cell">
                                        {{ $log->ip_address ?? '—' }}
                                    </td>
                                    <td class="p-4 text-right">
                                        @if (!empty($log->details))
                                            @php
                                                $logRoleName = $log->user?->getRoleNames()->first();
                                            @endphp
                                            <button type="button"
                                                    @click="openDetail({{ Js::from([
                                                        'id' => $log->id,
                                                        'time' => $log->created_at->locale('id')->translatedFormat('d M Y, H:i:s'),
                                                        'user_name' => $log->user?->name ?? 'Anonymous',
                                                        'username' => $log->user?->username,
                                                        'role' => $logRoleName,
                                                        'role_badge' => $logRoleName ? ($roleBadge[$logRoleName] ?? '') : 'bg-surface-container-high text-on-surface-variant border-outline-variant',
                                                        'action_label' => $meta['label'],
                                                        'action_raw' => $log->action,
                                                        'badge_class' => $badgeClass,
                                                        'ip' => $log->ip_address ?? '—',
                                                        'details' => $log->details,
                                                    ]) }})"
                                                    title="Lihat detail"
                                                    class="p-2 text-outline hover:text-primary transition-colors rounded-full hover:bg-surface-container-low inline-flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </button>
                                        @else
                                            <span class="text-outline text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="p-md border-t border-outline-variant flex flex-col sm:flex-row justify-between items-center gap-sm bg-surface-container-low">
                        <span class="text-sm text-on-surface-variant tabular-nums">
                            Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ number_format($logs->total()) }}
                        </span>
                        <div>
                            {{ $logs->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="p-xl flex flex-col items-center text-center gap-md">
                    <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center">
                        <span class="material-symbols-outlined text-outline" style="font-size: 36px;">{{ $hasFilter ? 'filter_alt_off' : 'history_toggle_off' }}</span>
                    </div>
                    <div>
                        @if ($hasFilter)
                            <h2 class="font-h2 text-h2 text-on-background mb-1">Tidak Ada Hasil</h2>
                            <p class="text-on-surface-variant">Filter yang Anda terapkan tidak menemukan log.</p>
                        @else
                            <h2 class="font-h2 text-h2 text-on-background mb-1">Belum Ada Aktivitas</h2>
                            <p class="text-on-surface-variant">Audit log akan otomatis tercatat saat admin melakukan aksi.</p>
                        @endif
                    </div>
                    @if ($hasFilter)
                        <a href="{{ route('admin.audit-logs.index') }}"
                           class="inline-flex items-center gap-2 text-primary font-bold hover:underline">
                            <span class="material-symbols-outlined text-[20px]">refresh</span>
                            Reset filter
                        </a>
                    @endif
                </div>
            @endif
        </section>

        {{-- ============================================================ --}}
        {{-- MODAL: DETAIL LOG (read-only JSON viewer)                   --}}
        {{-- ============================================================ --}}
        <div x-show="detail !== null" x-transition.opacity x-cloak
             @keydown.escape.window="detail = null"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="detail = null"></div>
            <div x-show="detail !== null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant shrink-0">
                    <h3 class="font-h2 text-h2 text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container">receipt_long</span>
                        Detail Aktivitas
                    </h3>
                    <button type="button" @click="detail = null"
                            class="p-1 rounded-full text-outline hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-lg flex flex-col gap-md overflow-y-auto">
                    {{-- Meta info grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-md">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Waktu</p>
                            <p class="font-medium text-on-surface" x-text="detail?.time"></p>
                        </div>
                        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-md">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">User</p>
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-on-surface" x-text="detail?.user_name"></p>
                                <template x-if="detail?.role">
                                    <span class="inline-flex items-center px-1.5 py-0 rounded border text-[10px] font-bold uppercase tracking-wider"
                                          :class="detail?.role_badge"
                                          x-text="detail?.role"></span>
                                </template>
                            </div>
                            <template x-if="detail?.username">
                                <p class="text-xs font-mono text-primary" x-text="detail?.username"></p>
                            </template>
                        </div>
                        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-md">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">Aksi</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wider"
                                  :class="detail?.badge_class"
                                  x-text="detail?.action_label"></span>
                            <p class="text-xs font-mono text-outline mt-1" x-text="detail?.action_raw"></p>
                        </div>
                        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-md">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1">IP Address</p>
                            <p class="font-mono text-on-surface" x-text="detail?.ip"></p>
                        </div>
                    </div>

                    {{-- Details JSON viewer --}}
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">data_object</span>
                            Detail Payload
                        </p>
                        <div class="bg-[#1E1B16] text-[#FFE173] rounded-xl p-md font-mono text-xs overflow-x-auto">
                            <pre x-text="JSON.stringify(detail?.details, null, 2)"></pre>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-md p-lg bg-surface-container-low border-t border-outline-variant shrink-0">
                    <button type="button"
                            @click="copyJson()"
                            class="inline-flex items-center gap-sm bg-white border border-outline-variant text-on-surface px-5 py-3 rounded-full font-bold hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-text="copied ? 'check' : 'content_copy'"></span>
                        <span x-text="copied ? 'Tersalin' : 'Salin JSON'"></span>
                    </button>
                    <button type="button" @click="detail = null"
                            class="px-5 py-3 rounded-full font-bold text-on-surface-variant hover:bg-surface-container transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function auditLogPage() {
            return {
                detail: null,
                copied: false,

                openDetail(log) {
                    this.detail = log;
                    this.copied = false;
                },

                copyJson() {
                    if (!this.detail) return;
                    navigator.clipboard.writeText(JSON.stringify(this.detail.details, null, 2)).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                },
            };
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        /* Pagination Laravel default — match design system */
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
