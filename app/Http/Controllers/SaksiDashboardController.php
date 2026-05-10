<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Vote;
use App\Models\VoterLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaksiDashboardController extends Controller
{
    public function show(?Election $election = null): View
    {
        // Default to latest active election if none specified
        if (!$election || !$election->exists) {
            $election = Election::where('status', 'active')->latest('start_date')->first()
                ?? Election::latest('created_at')->first();
        }

        if (!$election) {
            return view('saksi.dashboard', [
                'election' => null,
                'elections' => collect(),
                'totalDPT' => 0,
                'votedCount' => 0,
                'notVotedCount' => 0,
                'turnoutPercentage' => 0,
                'candidateResults' => [],
                'turnoutPerAngkatan' => [],
                'votingPerHour' => array_fill(0, 24, 0),
                'activityFeed' => collect(),
            ]);
        }

        $elections = Election::orderByDesc('created_at')->get(['id', 'name', 'status']);

        $totalDPT = $election->voters()->count();
        $votedCount = $election->voters()->wherePivot('has_voted', true)->count();
        $notVotedCount = $totalDPT - $votedCount;
        $turnoutPercentage = $totalDPT > 0 ? round(($votedCount / $totalDPT) * 100, 1) : 0;

        // Candidate results
        $candidateResults = $election->candidates->map(function ($candidate) use ($election) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'votes' => Vote::where('election_id', $election->id)
                    ->where('candidate_id', $candidate->id)
                    ->count(),
            ];
        })->toArray();

        // Turnout per angkatan
        $turnoutPerAngkatan = DB::table('election_user')
            ->join('users', 'users.id', '=', 'election_user.user_id')
            ->where('election_user.election_id', $election->id)
            ->select('users.angkatan', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN election_user.has_voted = 1 THEN 1 ELSE 0 END) as voted'))
            ->groupBy('users.angkatan')
            ->orderBy('users.angkatan')
            ->get()
            ->map(function ($row) {
                return [
                    'angkatan' => $row->angkatan ?? 'N/A',
                    'total' => (int) $row->total,
                    'voted' => (int) $row->voted,
                    'percentage' => $row->total > 0 ? round(($row->voted / $row->total) * 100, 1) : 0,
                ];
            })->toArray();

        // Voting per hour
        $logs = VoterLog::where('election_id', $election->id)
            ->select(DB::raw('HOUR(voted_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(voted_at)'))
            ->orderBy('hour')
            ->get();

        $votingPerHour = array_fill(0, 24, 0);
        foreach ($logs as $log) {
            $votingPerHour[$log->hour] = (int) $log->count;
        }

        // Activity feed
        $activityFeed = VoterLog::where('election_id', $election->id)
            ->with('user:id,angkatan')
            ->orderByDesc('voted_at')
            ->limit(10)
            ->get();

        return view('saksi.dashboard', compact(
            'election',
            'elections',
            'totalDPT',
            'votedCount',
            'notVotedCount',
            'turnoutPercentage',
            'candidateResults',
            'turnoutPerAngkatan',
            'votingPerHour',
            'activityFeed',
        ));
    }
}
