<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use App\Services\ReportService;
use App\Traits\Auditable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    use Auditable;

    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $elections = Election::latest('start_date')->get();

        // Election context — dari query param atau default ke active/terbaru.
        $selectedElection = null;
        if ($request->filled('election')) {
            $selectedElection = Election::find($request->input('election'));
        }
        $selectedElection = $selectedElection
            ?? $elections->firstWhere('status', 'active')
            ?? $elections->first();

        // Export history dari audit_logs — filter generate_*_report.
        $exportHistory = AuditLog::with('user')
            ->whereIn('action', ['generate_attendance_report', 'generate_result_report'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        // Live Presentation: bisa launch kalau ada election dengan result_visibility=public.
        $publicResultElection = Election::where('result_visibility', 'public')
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'finished' THEN 2 ELSE 3 END")
            ->latest('start_date')
            ->first();

        return view('admin.reports.index', compact(
            'elections',
            'selectedElection',
            'exportHistory',
            'publicResultElection',
        ));
    }

    public function attendance(Election $election): Response
    {
        $this->audit('generate_attendance_report', [
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return $this->reportService->generateAttendanceReport($election, auth()->user());
    }

    public function result(Election $election): Response
    {
        $this->audit('generate_result_report', [
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return $this->reportService->generateResultReport($election, auth()->user());
    }
}
