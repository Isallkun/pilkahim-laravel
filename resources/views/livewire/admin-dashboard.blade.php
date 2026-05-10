<div wire:poll.5s class="space-y-xl">
    {{-- Header --}}
    <section class="space-y-1 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="font-h1 text-h1 text-primary leading-tight">Dashboard Live</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Pantau pelaksanaan pemilihan secara real-time</p>
        </div>

        {{-- Election selector (kalau ada > 1) --}}
        @if ($elections->count() > 1)
            <div class="flex items-center gap-2 bg-white border border-[#E5E0D5] rounded-2xl px-4 py-2 shadow-sm">
                <span class="material-symbols-outlined text-[#8E8676] text-[20px]">filter_list</span>
                <select wire:change="selectElection($event.target.value)"
                        class="bg-transparent border-0 focus:ring-0 text-sm font-medium text-on-background py-1 pr-8">
                    @foreach ($elections as $el)
                        <option value="{{ $el->id }}" @selected($el->id === $selectedElectionId)>
                            {{ $el->name }} ({{ ucfirst($el->status) }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </section>

    @if ($election)

        {{-- ROW 1: KPI Cards --}}
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-md">
            {{-- Total DPT --}}
            <div class="bg-white p-lg rounded-2xl border border-outline-variant/30 glow-shadow hover:border-primary-container transition-colors duration-300">
                <div class="flex items-center justify-between mb-sm">
                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider">Total DPT</span>
                    <div class="bg-surface-container w-10 h-10 rounded-full flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">groups</span>
                    </div>
                </div>
                <div class="font-display text-display-mobile text-on-background tabular-nums">{{ number_format($totalDPT) }}</div>
            </div>

            {{-- Sudah Voting --}}
            <div class="bg-white p-lg rounded-2xl border border-outline-variant/30 glow-shadow hover:border-primary-container transition-colors duration-300">
                <div class="flex items-center justify-between mb-sm">
                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider">Sudah Voting</span>
                    <div class="bg-surface-container w-10 h-10 rounded-full flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">how_to_vote</span>
                    </div>
                </div>
                <div class="flex items-end gap-sm">
                    <div class="font-display text-display-mobile text-on-background tabular-nums">{{ number_format($votedCount) }}</div>
                    <div class="font-body-md text-body-md text-primary bg-primary-fixed/30 px-sm py-xs rounded-lg mb-sm font-bold">{{ $turnoutPercentage }}%</div>
                </div>
            </div>

            {{-- Belum Voting --}}
            <div class="bg-white p-lg rounded-2xl border border-outline-variant/30 glow-shadow hover:border-primary-container transition-colors duration-300">
                <div class="flex items-center justify-between mb-sm">
                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider">Belum Voting</span>
                    <div class="bg-surface-container w-10 h-10 rounded-full flex items-center justify-center text-outline">
                        <span class="material-symbols-outlined">person_off</span>
                    </div>
                </div>
                <div class="font-display text-display-mobile text-on-background tabular-nums">{{ number_format($notVotedCount) }}</div>
            </div>

            {{-- Sisa Waktu --}}
            @if ($election->status === 'active' && $election->end_date)
                <div class="bg-white p-lg rounded-2xl border border-error-container glow-shadow hover:border-error transition-colors duration-300 relative overflow-hidden"
                     x-data="adminCountdown('{{ $election->end_date->toIso8601String() }}')" x-init="start()">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-error-container/20 rounded-full blur-xl"></div>
                    <div class="flex items-center justify-between mb-sm relative z-10">
                        <span class="font-label-caps text-label-caps text-error uppercase tracking-wider font-bold">Sisa Waktu</span>
                        <div class="bg-error-container w-10 h-10 rounded-full flex items-center justify-center text-error">
                            <span class="material-symbols-outlined">timer</span>
                        </div>
                    </div>
                    <div class="font-display text-display-mobile text-error relative z-10 tabular-nums" x-text="display">--:--:--</div>
                </div>
            @else
                <div class="bg-white p-lg rounded-2xl border border-outline-variant/30 glow-shadow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-sm relative z-10">
                        <span class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider font-bold">Status</span>
                        <div class="bg-surface-container w-10 h-10 rounded-full flex items-center justify-center text-outline">
                            <span class="material-symbols-outlined">{{ $election->status === 'completed' ? 'task_alt' : 'schedule' }}</span>
                        </div>
                    </div>
                    <div class="font-h1 text-h1 text-on-background relative z-10 capitalize">
                        {{ $election->status === 'completed' ? 'Selesai' : ucfirst($election->status) }}
                    </div>
                </div>
            @endif
        </section>

        {{-- ROW 2: Donut chart + Activity feed --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-md">
            {{-- Donut --}}
            <div class="lg:col-span-1 bg-white p-lg rounded-3xl border border-outline-variant/30 glow-shadow">
                <h3 class="font-h2 text-h2 text-on-background mb-lg">Perolehan Suara Real-time</h3>
                {{-- wire:ignore mencegah Livewire morph canvas — Chart.js update via dispatched event --}}
                <div wire:ignore class="relative" style="height: 230px;"
                     x-data='dashboardCharts(@json($candidateResults))' x-init="init()">
                    <canvas x-ref="donutChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="font-body-md text-on-surface-variant text-sm">Total Masuk</span>
                        <span class="font-h1 text-h1 text-primary tabular-nums">{{ number_format($votedCount) }}</span>
                    </div>
                </div>
                <div class="space-y-sm mt-lg">
                    @php
                        $palette = ['#e5a100', '#ffba36', '#ffe083', '#d1aa00', '#7e5700', '#fdd73b', '#705d00'];
                        $sumVotes = max(1, array_sum(array_column($candidateResults, 'votes')));
                    @endphp
                    @foreach ($candidateResults as $i => $c)
                        @php $pct = round(($c['votes'] / $sumVotes) * 100); @endphp
                        <div class="flex justify-between items-center bg-surface-container-low p-sm rounded-lg">
                            <div class="flex items-center gap-sm min-w-0">
                                <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $palette[$i % count($palette)] }}"></div>
                                <span class="font-body-md truncate">{{ $c['name'] }}</span>
                            </div>
                            <span class="font-bold tabular-nums shrink-0 ml-2">{{ $pct }}% ({{ number_format($c['votes']) }})</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Activity --}}
            <div class="lg:col-span-2 bg-white p-lg rounded-3xl border border-outline-variant/30 glow-shadow flex flex-col">
                <div class="flex justify-between items-center mb-lg">
                    <h3 class="font-h2 text-h2 text-on-background">Aktivitas Voting</h3>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-primary font-bold hover:text-primary-container transition-colors text-sm uppercase tracking-wider">Lihat Semua</a>
                </div>
                <div class="flex-1 space-y-0 relative">
                    {{-- Timeline line --}}
                    <div class="absolute left-[19px] top-4 bottom-4 w-px bg-outline-variant/30"></div>

                    @forelse ($activityFeed as $log)
                        <div class="relative flex gap-md p-md hover:bg-surface-container-low transition-colors rounded-xl group">
                            <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center shrink-0 z-10 border-4 border-white group-hover:border-surface-container-low transition-colors text-primary">
                                <span class="material-symbols-outlined text-[20px]">how_to_vote</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <p class="font-bold text-on-background truncate">Mahasiswa Angkatan {{ $log->user?->angkatan ?? '?' }}</p>
                                    <span class="text-sm text-on-surface-variant shrink-0">{{ $log->voted_at?->diffForHumans() }}</span>
                                </div>
                                <p class="text-on-surface-variant mt-xs text-sm">Berhasil melakukan voting</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <span class="material-symbols-outlined text-[#D5C4AD]" style="font-size: 64px;">inbox</span>
                            <p class="text-on-surface-variant mt-2 font-medium">Belum ada aktivitas voting.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ROW 3: Bar chart angkatan --}}
        <section class="bg-white p-lg rounded-3xl border border-outline-variant/30 glow-shadow">
            <h3 class="font-h2 text-h2 text-on-background mb-lg">Partisipasi per Angkatan</h3>
            @if (count($turnoutPerAngkatan) > 0)
                <div class="space-y-md">
                    @foreach ($turnoutPerAngkatan as $row)
                        <div>
                            <div class="flex justify-between items-end mb-xs gap-2">
                                <span class="font-bold">Angkatan {{ $row['angkatan'] }}</span>
                                <span class="text-sm text-on-surface-variant tabular-nums">{{ number_format($row['voted']) }} / {{ number_format($row['total']) }} ({{ $row['percentage'] }}%)</span>
                            </div>
                            <div class="h-4 bg-surface-container rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-primary-fixed to-primary-container rounded-full transition-all duration-500" style="width: {{ $row['percentage'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <span class="material-symbols-outlined text-[#D5C4AD]" style="font-size: 48px;">bar_chart</span>
                    <p class="text-on-surface-variant mt-2 text-sm">Belum ada data partisipasi.</p>
                </div>
            @endif
        </section>

        {{-- ROW 4: Line chart - tren per jam --}}
        <section class="bg-white p-lg rounded-3xl border border-outline-variant/30 glow-shadow">
            <h3 class="font-h2 text-h2 text-on-background mb-lg">Tren Voting Hari Ini</h3>
            <div wire:ignore class="relative" style="height: 240px;"
                 x-data='activityChart(@json($votingPerHour))' x-init="init()">
                <canvas x-ref="lineChart"></canvas>
            </div>
        </section>

        {{-- ROW 5: Quick Actions --}}
        <section class="flex flex-wrap gap-md justify-start md:justify-end">
            @if ($election->status === 'active')
                <a href="{{ route('admin.elections.edit', $election) }}"
                   class="bg-error text-on-error px-6 py-4 rounded-full font-bold hover:bg-on-error-container transition-colors flex items-center gap-sm shadow-md h-14">
                    <span class="material-symbols-outlined">block</span>
                    Tutup Voting
                </a>
            @endif

            <a href="{{ route('admin.reports.result', $election) }}"
               class="bg-secondary-container text-on-secondary-container px-6 py-4 rounded-full font-bold hover:bg-secondary-fixed-dim transition-colors flex items-center gap-sm shadow-md h-14">
                <span class="material-symbols-outlined">download</span>
                Export Hasil
            </a>

            <a href="{{ route('admin.reports.attendance', $election) }}"
               class="bg-surface-container-highest text-on-surface px-6 py-4 rounded-full font-bold hover:bg-surface-dim transition-colors flex items-center gap-sm border border-outline-variant/50 h-14">
                <span class="material-symbols-outlined">fact_check</span>
                Daftar Hadir
            </a>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="bg-surface-container-highest text-on-surface px-6 py-4 rounded-full font-bold hover:bg-surface-dim transition-colors flex items-center gap-sm border border-outline-variant/50 h-14">
                <span class="material-symbols-outlined">history</span>
                Audit Log
            </a>
        </section>

    @else
        {{-- Empty state --}}
        <div class="bg-white p-12 rounded-3xl border border-outline-variant/30 glow-shadow text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-[#FEF9E7] flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[#E5A100]" style="font-size: 48px;">how_to_vote</span>
            </div>
            <h2 class="font-h2 text-h2 text-on-background mb-2">Belum Ada Event Pemilihan</h2>
            <p class="text-on-surface-variant mb-6">Buat event pemilihan terlebih dahulu untuk mulai memantau dashboard.</p>
            <a href="{{ route('admin.elections.create') }}" class="inline-flex items-center gap-2 bg-[#E5A100] text-white px-6 py-3 rounded-full font-bold hover:bg-[#D97706] transition-colors">
                <span class="material-symbols-outlined">add</span>
                Buat Event Baru
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Countdown — sisa waktu pemilihan
    function adminCountdown(endDate) {
        return {
            display: '--:--:--',
            interval: null,
            start() {
                this.tick(endDate);
                this.interval = setInterval(() => this.tick(endDate), 1000);
            },
            tick(endDate) {
                const now = new Date();
                const end = new Date(endDate);
                const diff = end - now;

                if (diff <= 0) {
                    this.display = 'Habis';
                    clearInterval(this.interval);
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                const pad = (n) => String(n).padStart(2, '0');

                this.display = days > 0
                    ? `${days}h ${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
                    : `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
            },
            destroy() { clearInterval(this.interval); }
        };
    }

    // Palette warna kandidat (bisa diakses kedua chart)
    const ARUTALA_PALETTE = ['#e5a100', '#ffba36', '#ffe083', '#d1aa00', '#7e5700', '#fdd73b', '#705d00'];

    // Donut chart — Perolehan suara real-time
    function dashboardCharts(initialData) {
        return {
            donutChart: null,
            init() {
                this.$nextTick(() => {
                    this.createDonutChart(initialData);
                    // Dengar event dari Livewire setiap kali render (termasuk wire:poll)
                    this.$wire.on('charts:refresh', (payload) => {
                        const data = payload.candidates ?? payload[0]?.candidates ?? [];
                        this.refreshDonut(data);
                    });
                });
            },
            createDonutChart(data) {
                const ctx = this.$refs.donutChart;
                if (!ctx || !window.Chart) return;

                this.donutChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.map(c => c.name),
                        datasets: [{
                            data: data.map(c => c.votes),
                            backgroundColor: ARUTALA_PALETTE.slice(0, data.length),
                            borderWidth: 4,
                            borderColor: '#ffffff',
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.label}: ${ctx.parsed} suara`
                                }
                            }
                        }
                    }
                });
            },
            refreshDonut(data) {
                if (!this.donutChart) return;
                this.donutChart.data.labels = data.map(c => c.name);
                this.donutChart.data.datasets[0].data = data.map(c => c.votes);
                this.donutChart.data.datasets[0].backgroundColor = ARUTALA_PALETTE.slice(0, data.length);
                this.donutChart.update('none');
            },
        };
    }

    // Line chart — Tren voting per jam
    function activityChart(initialData) {
        return {
            lineChart: null,
            init() {
                this.$nextTick(() => {
                    this.createLineChart(initialData);
                    this.$wire.on('charts:refresh', (payload) => {
                        const data = payload.hourly ?? payload[0]?.hourly ?? [];
                        this.refreshLine(data);
                    });
                });
            },
            createLineChart(data) {
                const ctx = this.$refs.lineChart;
                if (!ctx || !window.Chart) return;
                const labels = Array.from({length: 24}, (_, i) => String(i).padStart(2, '0') + ':00');

                this.lineChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Vote',
                            data: data,
                            borderColor: '#E5A100',
                            backgroundColor: 'rgba(229, 161, 0, 0.15)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#E5A100',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            borderWidth: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: '#8E8676' },
                                grid: { color: '#F4EDE3' }
                            },
                            x: {
                                ticks: { color: '#8E8676' },
                                grid: { display: false }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.parsed.y} suara`
                                }
                            }
                        }
                    }
                });
            },
            refreshLine(data) {
                if (!this.lineChart) return;
                this.lineChart.data.datasets[0].data = data;
                this.lineChart.update('none');
            },
        };
    }
</script>
@endpush
