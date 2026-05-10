<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil - {{ $election->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .header h2 {
            font-size: 13px;
            font-weight: normal;
            color: #444;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .meta {
            margin-bottom: 16px;
            font-size: 10px;
            color: #555;
        }
        .meta table {
            border: none;
        }
        .meta td {
            padding: 2px 8px 2px 0;
            border: none;
        }
        .meta .label {
            font-weight: bold;
            width: 120px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        table.data th,
        table.data td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        table.data th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.data td {
            font-size: 10px;
        }
        table.data tr:nth-child(even) {
            background-color: #fafafa;
        }
        .summary {
            margin-top: 16px;
            font-size: 11px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #888;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .percentage {
            color: #555;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $appName }}</h1>
        <h2>Laporan Hasil Pemilihan</h2>
        <p>{{ $election->name }}</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Election</td>
                <td>: {{ $election->name }}</td>
            </tr>
            <tr>
                <td class="label">Periode</td>
                <td>: {{ $election->start_date->format('d M Y H:i') }} — {{ $election->end_date->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td>: {{ ucfirst($election->status) }}</td>
            </tr>
            <tr>
                <td class="label">Digenerate oleh</td>
                <td>: {{ $generatedBy->name }} ({{ $generatedBy->username }})</td>
            </tr>
            <tr>
                <td class="label">Waktu generate</td>
                <td>: {{ $generatedAt->format('d M Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Kandidat</th>
                <th style="width: 80px; text-align: center;">Jumlah Suara</th>
                <th style="width: 80px; text-align: center;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($candidates as $index => $candidate)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $candidate->name }}</td>
                    <td style="text-align: center;">{{ $candidate->votes_count }}</td>
                    <td style="text-align: center;">
                        {{ $totalVotes > 0 ? number_format(($candidate->votes_count / $totalVotes) * 100, 1) : '0.0' }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #888;">Belum ada data kandidat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        Total suara masuk: {{ $totalVotes }} suara
    </div>

    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh sistem {{ $appName }} pada {{ $generatedAt->format('d M Y H:i:s') }}
    </div>
</body>
</html>
