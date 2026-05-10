<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Vote;
use App\Models\VoterLog;
use App\Traits\Auditable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    use Auditable;

    /**
     * Show settings page (per-election context).
     */
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

        $voteStats = null;
        if ($selectedElection) {
            $voteStats = [
                'total_votes' => Vote::where('election_id', $selectedElection->id)->count(),
                'voter_logs' => VoterLog::where('election_id', $selectedElection->id)->count(),
                'voted_pivot' => $selectedElection->voters()->wherePivot('has_voted', true)->count(),
            ];
        }

        return view('admin.settings.index', compact('elections', 'selectedElection', 'voteStats'));
    }

    /**
     * Toggle visibility hasil live count ke publik.
     * Beda dengan ElectionController::toggleResults yang hanya boleh saat finished —
     * di sini boleh toggle kapan saja (termasuk active) supaya bisa stream live count
     * sambil voting jalan.
     */
    public function updateResultVisibility(Request $request, Election $election): RedirectResponse
    {
        $request->validate([
            'result_visibility' => ['required', 'in:public,private'],
        ]);

        $election->update(['result_visibility' => $request->input('result_visibility')]);

        $this->audit('toggle_result_visibility', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            'result_visibility' => $election->result_visibility,
        ]);

        $msg = $election->result_visibility === 'public'
            ? 'Live count hasil sekarang TAMPIL ke publik (URL /results/' . $election->id . ').'
            : 'Live count hasil sekarang DISEMBUNYIKAN dari publik.';

        return redirect()->route('admin.settings.index', ['election' => $election->id])
            ->with('success', $msg);
    }

    /**
     * Toggle visibility countdown sisa waktu pemilihan.
     */
    public function updateCountdown(Request $request, Election $election): RedirectResponse
    {
        $request->validate([
            'show_countdown' => ['required', 'boolean'],
        ]);

        $election->update(['show_countdown' => $request->boolean('show_countdown')]);

        $this->audit('toggle_countdown', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            'show_countdown' => $election->show_countdown,
        ]);

        $msg = $election->show_countdown
            ? 'Countdown sisa waktu sekarang TAMPIL ke publik.'
            : 'Countdown sisa waktu sekarang DISEMBUNYIKAN dari publik.';

        return redirect()->route('admin.settings.index', ['election' => $election->id])
            ->with('success', $msg);
    }

    /**
     * Tutup voting — transition status dari 'active' ke 'finished'.
     * Aksi semi-destruktif (one-way: setelah finished, suara final).
     * Data TIDAK dihapus, cuma status berubah.
     */
    public function closeVoting(Request $request, Election $election): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:TUTUP'],
        ], [
            'confirmation.required' => 'Konfirmasi wajib diisi.',
            'confirmation.in' => 'Konfirmasi harus persis "TUTUP".',
        ]);

        if ($election->status !== 'active') {
            return redirect()->route('admin.settings.index', ['election' => $election->id])
                ->with('error', 'Tutup voting hanya bisa dilakukan saat status pemilihan aktif.');
        }

        $previousStatus = $election->status;
        $election->update(['status' => 'finished']);

        $this->audit('close_voting', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            'previous_status' => $previousStatus,
            'new_status' => 'finished',
        ]);

        return redirect()->route('admin.settings.index', ['election' => $election->id])
            ->with('success', "Voting untuk '{$election->name}' berhasil ditutup. Pemilih tidak bisa lagi vote.");
    }

    /**
     * Reset semua suara untuk election ini.
     *  - Hapus records di tabel votes
     *  - Hapus voter_logs
     *  - Reset has_voted=false di pivot election_user
     *
     * Aksi destruktif — butuh body field `confirmation` = "RESET SUARA".
     */
    public function destroyVotes(Request $request, Election $election): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:RESET SUARA'],
        ], [
            'confirmation.required' => 'Konfirmasi wajib diisi.',
            'confirmation.in' => 'Konfirmasi harus persis "RESET SUARA".',
        ]);

        if ($election->status === 'finished') {
            return redirect()->route('admin.settings.index', ['election' => $election->id])
                ->with('error', 'Tidak bisa reset suara untuk pemilihan yang sudah selesai.');
        }

        $stats = ['votes_deleted' => 0, 'logs_deleted' => 0, 'pivot_reset' => 0];

        DB::transaction(function () use ($election, &$stats) {
            $stats['votes_deleted'] = Vote::where('election_id', $election->id)->delete();
            $stats['logs_deleted'] = VoterLog::where('election_id', $election->id)->delete();
            $stats['pivot_reset'] = $election->voters()->newPivotQuery()->update(['has_voted' => false]);
        });

        $this->audit('reset_all_votes', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            ...$stats,
        ]);

        return redirect()->route('admin.settings.index', ['election' => $election->id])
            ->with('success', "Reset selesai: {$stats['votes_deleted']} suara dihapus, {$stats['pivot_reset']} pemilih dikembalikan ke status belum-vote.");
    }
}
