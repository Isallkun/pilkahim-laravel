<?php

namespace App\Services;

use App\Models\Election;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportService
{
    public function generateAttendanceReport(Election $election, User $generatedBy): Response
    {
        $voterLogs = $election->voterLogs()
            ->with('user')
            ->orderBy('voted_at')
            ->get();

        $data = [
            'election' => $election,
            'voterLogs' => $voterLogs,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
            'appName' => 'E-Vote Arutala IAIC Pasuruan Lead 2026',
        ];

        $filename = 'laporan-kehadiran-' . str()->slug($election->name) . '-' . now()->format('Ymd-His') . '.pdf';

        return Pdf::loadView('reports.attendance', $data)->download($filename);
    }

    public function generateResultReport(Election $election, User $generatedBy): Response
    {
        $candidates = $election->candidates()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->get();

        $totalVotes = $election->votes()->count();

        $data = [
            'election' => $election,
            'candidates' => $candidates,
            'totalVotes' => $totalVotes,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
            'appName' => 'E-Vote Arutala IAIC Pasuruan Lead 2026',
        ];

        $filename = 'laporan-hasil-' . str()->slug($election->name) . '-' . now()->format('Ymd-His') . '.pdf';

        return Pdf::loadView('reports.result', $data)->download($filename);
    }
}
