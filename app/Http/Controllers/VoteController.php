<?php

namespace App\Http\Controllers;

use App\Exceptions\AlreadyVotedException;
use App\Exceptions\ElectionNotActiveException;
use App\Http\Requests\SubmitVoteRequest;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\VoterLog;
use App\Services\VotingService;
use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VoteController extends Controller
{
    use Auditable;

    public function __construct(private VotingService $votingService) {}

    /**
     * GET /vote/{election} — Show ballot with all candidates.
     */
    public function showBallot(Election $election): View|RedirectResponse
    {
        // Check election is active
        if (!$election->isActive()) {
            return redirect()->route('home')
                ->with('error', 'Pemilihan belum dimulai atau sudah berakhir.');
        }

        $user = auth()->user();

        // Already voted → render layar receipt yang sama (dipakai juga sehabis vote)
        if ($this->votingService->hasVoted($user, $election)) {
            $voterLog = VoterLog::where('user_id', $user->id)
                ->where('election_id', $election->id)
                ->latest('voted_at')
                ->first();

            $votedAt = $voterLog
                ? Carbon::parse($voterLog->voted_at)->locale('id')->translatedFormat('d F Y, H:i') . ' WIB'
                : '—';

            // Audit "view already voted" — throttled 1 jam per user-election supaya
            // tidak spam (user yang refresh berkali-kali cuma 1 entry per jam).
            $recentAlreadyVotedLog = AuditLog::where('user_id', $user->id)
                ->where('action', 'view_already_voted')
                ->whereJsonContains('details->election_id', $election->id)
                ->where('created_at', '>=', now()->subHour())
                ->exists();

            if (!$recentAlreadyVotedLog) {
                $this->audit('view_already_voted', [
                    'username' => $user->username,
                    'name' => $user->name,
                    'election_id' => $election->id,
                    'election_name' => $election->name,
                    'voted_at' => $voterLog?->voted_at?->toDateTimeString(),
                ]);
            }

            return view('vote.already-voted', [
                'user' => $user,
                'election' => $election,
                'votedAt' => $votedAt,
            ]);
        }

        // Load candidates ordered by sort_order
        $election->load('candidates');

        return view('vote.ballot', [
            'election' => $election,
            'candidates' => $election->candidates,
            'candidatesJson' => $election->candidates->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'sort_order' => $c->sort_order,
                    'visi' => $c->visi,
                    'misi' => $c->misi,
                    'video_url' => $c->video_url,
                ];
            }),
        ]);
    }

    /**
     * POST /vote/{election}/submit — Process the vote.
     * Setelah berhasil, redirect ke ballot yang akan auto-render layar receipt
     * lewat cek hasVoted() — biar layar post-vote dan revisit konsisten.
     */
    public function submitVote(SubmitVoteRequest $request, Election $election): RedirectResponse
    {
        $user = auth()->user();
        $candidate = Candidate::findOrFail($request->validated('candidate_id'));

        try {
            $this->votingService->castVote($user, $election, $candidate);
        } catch (AlreadyVotedException $e) {
            // Security event: pemilih nekat submit padahal sudah vote.
            // Tidak throttled — setiap attempt direkam.
            $this->audit('duplicate_vote_attempt', [
                'username' => $user->username,
                'name' => $user->name,
                'election_id' => $election->id,
                'election_name' => $election->name,
            ]);

            return redirect()->route('vote.ballot', $election)
                ->with('error', $e->getMessage());
        } catch (ElectionNotActiveException $e) {
            return redirect()->route('home')
                ->with('error', $e->getMessage());
        }

        // Audit submit vote — sengaja TIDAK include candidate_id supaya tetap anonim.
        // Cuma fakta "user X partisipasi di election Y" yang tercatat.
        $this->audit('submit_vote', [
            'username' => $user->username,
            'name' => $user->name,
            'angkatan' => $user->angkatan,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('vote.ballot', $election);
    }
}
