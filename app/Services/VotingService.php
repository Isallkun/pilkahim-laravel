<?php

namespace App\Services;

use App\Exceptions\AlreadyVotedException;
use App\Exceptions\ElectionNotActiveException;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoterLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VotingService
{
    /**
     * Proses voting dengan transaksi atomik.
     * 
     * Flow:
     * 1. Cek election aktif
     * 2. Lock pivot record (cegah race condition)
     * 3. Cek belum voting
     * 4. Insert ke votes (TANPA user_id — anonim)
     * 5. Insert ke voter_logs (TANPA candidate_id — anonim)
     * 6. Update pivot has_voted = true
     * 7. Generate evidence token
     *
     * @return string Evidence token (16 char hex uppercase)
     */
    public function castVote(User $user, Election $election, Candidate $candidate): string
    {
        // Validasi election aktif
        if (!$election->isActive()) {
            throw new ElectionNotActiveException();
        }

        $evidenceToken = $this->generateEvidenceToken();

        DB::transaction(function () use ($user, $election, $candidate, $evidenceToken) {
            // Lock pivot record untuk cegah race condition (double-click)
            $pivot = DB::table('election_user')
                ->where('election_id', $election->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$pivot) {
                throw new \RuntimeException('User tidak terdaftar di DPT election ini.');
            }

            if ($pivot->has_voted) {
                throw new AlreadyVotedException();
            }

            // Double-check: cek juga voter_logs (safety net kalau pivot nggak ke-update)
            $existingLog = VoterLog::where('user_id', $user->id)
                ->where('election_id', $election->id)
                ->exists();

            if ($existingLog) {
                // Fix pivot yang nggak ke-update
                DB::table('election_user')
                    ->where('election_id', $election->id)
                    ->where('user_id', $user->id)
                    ->update(['has_voted' => true]);
                throw new AlreadyVotedException();
            }

            // Insert vote — ANONIM (tanpa user_id)
            Vote::create([
                'election_id' => $election->id,
                'candidate_id' => $candidate->id,
                'voted_at' => now(),
            ]);

            // Insert voter log — ANONIM (tanpa candidate_id)
            VoterLog::create([
                'user_id' => $user->id,
                'election_id' => $election->id,
                'evidence_token' => $evidenceToken,
                'ip_address' => request()->ip(),
                'voted_at' => now(),
            ]);

            // Mark as voted di pivot
            DB::table('election_user')
                ->where('election_id', $election->id)
                ->where('user_id', $user->id)
                ->update(['has_voted' => true]);

            // Update flag di user juga (untuk quick check)
            $user->update(['has_voted' => true]);
        });

        return $evidenceToken;
    }

    /**
     * Cek apakah user sudah voting di election tertentu.
     */
    public function hasVoted(User $user, Election $election): bool
    {
        return DB::table('election_user')
            ->where('election_id', $election->id)
            ->where('user_id', $user->id)
            ->where('has_voted', true)
            ->exists();
    }

    /**
     * Generate evidence token unik.
     * Format: 16 karakter hexadecimal uppercase (contoh: "A3F2B1C9D4E5F678")
     */
    public function generateEvidenceToken(): string
    {
        $hash = hash('sha256', Str::uuid()->toString() . microtime(true) . random_bytes(32));
        return strtoupper(substr($hash, 0, 16));
    }
}
