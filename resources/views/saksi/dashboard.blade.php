@extends('layouts.app')

@section('title', 'Dashboard Saksi')

@section('content')
    {{-- Election Selector --}}
    @if ($elections->count() > 1)
        <div class="mb-6">
            <form method="GET" id="election-selector-form">
                <select onchange="window.location.href='/saksi/dashboard/' + this.value"
                        class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    @foreach ($elections as $el)
                        <option value="{{ $el->id }}" @selected($election && $el->id === $election->id)>
                            {{ $el->name }} ({{ ucfirst($el->status) }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    @if ($election)
        {{-- Countdown Timer --}}
        @if ($election->status === 'active' && $election->end_date)
            <div class="mb-6" x-data="saksiCountdown('{{ $election->end_date->toIso8601String() }}')" x-init="start()">
                <x-card>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Waktu Tersisa</p>
                            <p class="text-2xl font-bold text-gray-900" x-text="display"></p>
                        </div>
                        <x-badge type="active">AKTIF</x-badge>
                    </div>
                </x-card>
            </div>
        @elseif ($election->status === 'completed')
            <div class="mb-6">
                <x-card>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">Status Pemilihan</p>
                        <x-badge type="finished">SELESAI</x-badge>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <x-kpi-card label="Total DPT" :value="number_format($totalDPT)" color="blue">
                <x-slot:icon>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-1.053M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card label="Sudah Voting" :value="number_format($votedCount)" color="green">
                <x-slot:icon>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card label="Belum Voting" :value="number_format($notVotedCount)" color="red">
                <x-slot:icon>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card label="Turnout" :value="$turnoutPercentage . '%'" color="primary">
                <x-slot:icon>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </x-slot:icon>
            </x-kpi-card>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Donut Chart: Vote Distribution --}}
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Suara</h3>
                <div class="relative" style="height: 300px;">
                    <canvas id="saksi-donut-chart"></canvas>
                </div>
            </x-card>

            {{-- Bar Chart: Turnout per Angkatan --}}
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Turnout per Angkatan</h3>
                <div class="relative" style="height: 300px;">
                    <canvas id="saksi-bar-chart"></canvas>
                </div>
            </x-card>
        </div>

        {{-- Line Chart: Voting Activity per Hour --}}
        <div class="mb-6">
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Voting per Jam</h3>
                <div class="relative" style="height: 250px;">
                    <canvas id="saksi-line-chart"></canvas>
                </div>
            </x-card>
        </div>

        {{-- Activity Feed --}}
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-3">
                @forelse ($activityFeed as $log)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                Mhs <span class="font-semibold">Angkatan {{ $log->user?->angkatan ?? '?' }}</span> baru saja voting
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="text-xs text-gray-500">{{ $log->voted_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada aktivitas voting.</p>
                @endforelse
            </div>
        </x-card>

        {{-- Auto-refresh notice --}}
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-400">Halaman ini akan refresh otomatis setiap 30 detik</p>
        </div>
    @else
        <x-card>
            <p class="text-center text-gray-500">Belum ada election aktif.</p>
        </x-card>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Countdown timer
    function saksiCountdown(endDate) {
        return {
            display: '',
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
                    this.display = 'Waktu habis!';
                    clearInterval(this.interval);
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                if (days > 0) {
                    this.display = `${days}h ${hours}j ${minutes}m ${seconds}d`;
                } else {
                    this.display = `${hours}j ${minutes}m ${seconds}d`;
                }
            },
            destroy() {
                clearInterval(this.interval);
            }
        };
    }

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        const candidateResults = @json($candidateResults);
        const turnoutPerAngkatan = @json($turnoutPerAngkatan);
        const votingPerHour = @json($votingPerHour);
        const colors = ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

        // Donut Chart
        const donutCtx = document.getElementById('saksi-donut-chart');
        if (donutCtx && candidateResults.length > 0) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: candidateResults.map(c => c.name),
                    datasets: [{
                        data: candidateResults.map(c => c.votes),
                        backgroundColor: colors.slice(0, candidateResults.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 16, usePointStyle: true }
                        }
                    }
                }
            });
        }

        // Bar Chart
        const barCtx = document.getElementById('saksi-bar-chart');
        if (barCtx && turnoutPerAngkatan.length > 0) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: turnoutPerAngkatan.map(d => 'Angkatan ' + d.angkatan),
                    datasets: [{
                        label: 'Turnout (%)',
                        data: turnoutPerAngkatan.map(d => d.percentage),
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { callback: v => v + '%' }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Line Chart
        const lineCtx = document.getElementById('saksi-line-chart');
        if (lineCtx) {
            const labels = Array.from({length: 24}, (_, i) => String(i).padStart(2, '0') + ':00');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Vote',
                        data: votingPerHour,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: '#f59e0b',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Auto-refresh every 30 seconds
        setTimeout(() => window.location.reload(), 30000);
    });
</script>
@endpush
