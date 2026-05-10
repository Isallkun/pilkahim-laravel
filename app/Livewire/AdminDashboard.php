<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\Vote;
use App\Models\VoterLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard Live')]
class AdminDashboard extends Component
{
    public ?int $selectedElectionId = null;

    public function mount(): void
    {
        // Default to the latest active election, or the most recent one
        $election = Election::where('status', 'active')->latest('start_date')->first()
            ?? Election::latest('created_at')->first();

        $this->selectedElectionId = $election?->id;
    }

    public function selectElection(int $id): void
    {
        $this->selectedElectionId = $id;
    }

    public function getElectionProperty(): ?Election
    {
        if (!$this->selectedElectionId) {
            return null;
        }

        return Election::with('candidates')->find($this->selectedElectionId);
    }

    public function getElectionsProperty()
    {
        return Election::orderByDesc('created_at')->get(['id', 'name', 'status']);
    }

    public function getTotalDPTProperty(): int
    {
        if (!$this->election) return 0;

        return $this->election->voters()->count();
    }

    public function getVotedCountProperty(): int
    {
        if (!$this->election) return 0;

        return $this->election->voters()->wherePivot('has_voted', true)->count();
    }

    public function getNotVotedCountProperty(): int
    {
        return $this->totalDPT - $this->votedCount;
    }

    public function getTurnoutPercentageProperty(): float
    {
        if ($this->totalDPT === 0) return 0;

        return round(($this->votedCount / $this->totalDPT) * 100, 1);
    }

    public function getCandidateResultsProperty(): array
    {
        if (!$this->election) return [];

        return $this->election->candidates->map(function ($candidate) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'votes' => Vote::where('election_id', $this->selectedElectionId)
                    ->where('candidate_id', $candidate->id)
                    ->count(),
            ];
        })->toArray();
    }

    public function getTurnoutPerAngkatanProperty(): array
    {
        if (!$this->election) return [];

        $voters = DB::table('election_user')
            ->join('users', 'users.id', '=', 'election_user.user_id')
            ->where('election_user.election_id', $this->selectedElectionId)
            ->select('users.angkatan', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN election_user.has_voted = 1 THEN 1 ELSE 0 END) as voted'))
            ->groupBy('users.angkatan')
            ->orderBy('users.angkatan')
            ->get();

        return $voters->map(function ($row) {
            return [
                'angkatan' => $row->angkatan ?? 'N/A',
                'total' => (int) $row->total,
                'voted' => (int) $row->voted,
                'percentage' => $row->total > 0 ? round(($row->voted / $row->total) * 100, 1) : 0,
            ];
        })->toArray();
    }

    public function getVotingPerHourProperty(): array
    {
        if (!$this->election) return [];

        $logs = VoterLog::where('election_id', $this->selectedElectionId)
            ->select(DB::raw('HOUR(voted_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(voted_at)'))
            ->orderBy('hour')
            ->get();

        // Fill all 24 hours
        $hours = array_fill(0, 24, 0);
        foreach ($logs as $log) {
            $hours[$log->hour] = (int) $log->count;
        }

        return $hours;
    }

    public function getActivityFeedProperty()
    {
        if (!$this->election) return collect();

        return VoterLog::where('election_id', $this->selectedElectionId)
            ->with('user:id,angkatan')
            ->orderByDesc('voted_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        $candidateResults = $this->candidateResults;
        $votingPerHour = $this->votingPerHour;

        // Dispatch event setiap render (termasuk wire:poll) supaya Alpine bisa update chart
        // tanpa mengandalkan @json() yang ke-bake di server-side.
        $this->dispatch('charts:refresh',
            candidates: $candidateResults,
            hourly: $votingPerHour,
        );

        return view('livewire.admin-dashboard', [
            'election' => $this->election,
            'elections' => $this->elections,
            'totalDPT' => $this->totalDPT,
            'votedCount' => $this->votedCount,
            'notVotedCount' => $this->notVotedCount,
            'turnoutPercentage' => $this->turnoutPercentage,
            'candidateResults' => $candidateResults,
            'turnoutPerAngkatan' => $this->turnoutPerAngkatan,
            'votingPerHour' => $votingPerHour,
            'activityFeed' => $this->activityFeed,
        ])->layout('layouts.arutala-admin');
    }
}
