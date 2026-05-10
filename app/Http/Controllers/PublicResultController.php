<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicResultController extends Controller
{
    public function show(Election $election): View
    {
        if (!$election->isResultPublic()) {
            abort(403, 'Hasil pemilihan ini belum dipublikasikan.');
        }

        $election->load('candidates');
        $data = $this->buildLiveData($election);

        return view('results.public', array_merge(['election' => $election], $data));
    }

    /**
     * GET /results/{election}/live — JSON endpoint untuk polling 5 detik.
     */
    public function live(Election $election): JsonResponse
    {
        if (!$election->isResultPublic()) {
            abort(403);
        }

        $election->load('candidates');

        return response()->json($this->buildLiveData($election));
    }

    private function buildLiveData(Election $election): array
    {
        $totalVotes = Vote::where('election_id', $election->id)->count();
        $totalEligible = $election->voters()->count();
        $turnoutPercentage = $totalEligible > 0
            ? round(($totalVotes / $totalEligible) * 100, 1)
            : 0;

        $candidateResults = $election->candidates->map(function ($candidate) use ($election, $totalVotes) {
            $voteCount = Vote::where('election_id', $election->id)
                ->where('candidate_id', $candidate->id)
                ->count();

            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'sort_order' => $candidate->sort_order,
                'photo_url' => $candidate->photo_path ? Storage::url($candidate->photo_path) : null,
                'votes' => $voteCount,
                'percentage' => $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0,
            ];
        })->sortByDesc('votes')->values()->toArray();

        return [
            'totalVotes' => $totalVotes,
            'totalEligible' => $totalEligible,
            'turnoutPercentage' => $turnoutPercentage,
            'candidateCount' => $election->candidates->count(),
            'candidateResults' => $candidateResults,
            'electionEndDate' => $election->end_date?->toIso8601String(),
            'electionStatus' => $election->status,
        ];
    }
}
