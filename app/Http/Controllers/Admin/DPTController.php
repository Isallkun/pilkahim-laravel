<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportDPTRequest;
use App\Http\Requests\Admin\StoreVoterRequest;
use App\Http\Requests\Admin\UpdateVoterRequest;
use App\Models\Election;
use App\Models\User;
use App\Models\VoterLog;
use App\Services\DPTImportService;
use App\Traits\Auditable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DPTController extends Controller
{
    use Auditable;

    public function __construct(
        private DPTImportService $importService
    ) {}

    /**
     * Tampilkan daftar pemilih (DPT) untuk election tertentu.
     */
    public function index(Request $request, Election $election): View
    {
        $search = $request->input('search');

        $voters = $election->voters()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        $totalVoters = $election->voters()->count();
        $votedCount = $election->voters()->wherePivot('has_voted', true)->count();

        // Orphan pemilih = role pemilih tapi tidak attached ke election manapun (residu import lama).
        $orphanedPemilihCount = User::role('pemilih')->whereDoesntHave('elections')->count();

        return view('admin.dpt.index', compact('election', 'voters', 'search', 'totalVoters', 'votedCount', 'orphanedPemilihCount'));
    }

    /**
     * Form import sudah jadi modal di halaman index — direct visit di-redirect ke sana.
     */
    public function showImportForm(Election $election): RedirectResponse
    {
        return redirect()->route('admin.dpt.index', [$election, 'modal' => 'import']);
    }

    /**
     * Proses import DPT dari file Excel.
     */
    public function import(ImportDPTRequest $request, Election $election): View
    {
        $result = $this->importService->import($request->file('file'), $election);

        $this->audit('import_dpt', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            'file_name' => $request->file('file')->getClientOriginalName(),
        ]);

        return view('admin.dpt.import-result', compact('election', 'result'));
    }

    /**
     * Tambah pemilih baru (single user) lalu attach ke election.
     */
    public function storeUser(StoreVoterRequest $request, Election $election): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'angkatan' => $data['angkatan'],
            'gender' => $data['gender'] ?? null,
            'has_voted' => false,
        ]);

        $user->assignRole('pemilih');

        // Attach ke election dengan status belum vote.
        $election->voters()->attach($user->id, ['has_voted' => false]);

        $this->audit('create_voter', [
            'user_id' => $user->id,
            'username' => $user->username,
            'user_name' => $user->name,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.dpt.index', $election)
            ->with('success', "Pemilih {$user->name} ({$user->username}) berhasil ditambahkan.");
    }

    /**
     * Update data pemilih.
     */
    public function updateUser(UpdateVoterRequest $request, Election $election, User $user): RedirectResponse
    {
        $data = $request->validated();

        $updates = [
            'username' => $data['username'],
            'name' => $data['name'],
            'angkatan' => $data['angkatan'],
            'gender' => $data['gender'] ?? null,
        ];

        // Kalau password diisi, ganti. Kalau kosong, tetap pakai yang lama.
        if (!empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
            $updates['password_changed_at'] = null;
        }

        $user->update($updates);

        $this->audit('update_voter', [
            'user_id' => $user->id,
            'username' => $user->username,
            'user_name' => $user->name,
            'election_id' => $election->id,
            'election_name' => $election->name,
            'password_changed' => !empty($data['password']),
        ]);

        return redirect()->route('admin.dpt.index', $election)
            ->with('success', "Data pemilih {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus pemilih dari election (detach).
     * User tidak di-delete karena bisa terdaftar di election lain & ada FK constraint dari voter_logs.
     */
    public function destroyUser(Election $election, User $user): RedirectResponse
    {
        $election->voters()->detach($user->id);

        $this->audit('remove_voter', [
            'user_id' => $user->id,
            'username' => $user->username,
            'user_name' => $user->name,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.dpt.index', $election)
            ->with('success', "Pemilih {$user->name} dihapus dari DPT.");
    }

    /**
     * Hapus SEMUA pemilih dari election + bersihkan record user di DB.
     *
     * Strategi (atomic via DB::transaction):
     *  1. Filter HANYA users dengan role 'pemilih' (admin/panitia/saksi AMAN, beda role)
     *  2. Kumpulkan: yang attached ke election ini + orphan pemilih (tidak attached ke election manapun)
     *  3. Hapus voter_logs terkait (FK ON DELETE NO ACTION → harus manual)
     *  4. Detach dari pivot election ini
     *  5. Delete user record yang sekarang TIDAK terdaftar di election lain
     *     (user yang masih ada di election lain → cuma di-detach, record tetap)
     *  6. Spatie Permission's model_has_roles auto-cleanup via Eloquent delete event
     *
     * Safety: butuh body field `confirmation` = "HAPUS SEMUA".
     */
    public function destroyAllUsers(Request $request, Election $election): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:HAPUS SEMUA'],
        ], [
            'confirmation.required' => 'Konfirmasi wajib diisi.',
            'confirmation.in' => 'Konfirmasi harus persis "HAPUS SEMUA".',
        ]);

        if ($election->status === 'finished') {
            return redirect()->route('admin.dpt.index', $election)
                ->with('error', 'Tidak bisa hapus DPT untuk pemilihan yang sudah selesai.');
        }

        $stats = ['attached_removed' => 0, 'orphans_removed' => 0, 'users_deleted' => 0];

        DB::transaction(function () use ($election, &$stats) {
            // 1. User IDs (role pemilih) yang attached ke election ini.
            $attachedIds = $election->voters()
                ->whereHas('roles', fn ($q) => $q->where('name', 'pemilih'))
                ->pluck('users.id')
                ->toArray();

            // 2. Orphan pemilih: role pemilih tapi tidak terdaftar di election manapun.
            $orphanIds = User::role('pemilih')
                ->whereDoesntHave('elections')
                ->pluck('id')
                ->toArray();

            $stats['attached_removed'] = count($attachedIds);
            $stats['orphans_removed'] = count($orphanIds);

            $allTargetIds = array_values(array_unique([...$attachedIds, ...$orphanIds]));

            if (empty($allTargetIds)) {
                return;
            }

            // 3. Hapus voter_logs untuk election ini (FK NO ACTION → manual cleanup).
            VoterLog::where('election_id', $election->id)
                ->whereIn('user_id', $allTargetIds)
                ->delete();

            // 4. Detach dari election ini.
            if (!empty($attachedIds)) {
                $election->voters()->detach($attachedIds);
            }

            // 5. Delete user record yang sekarang gak terdaftar di election manapun.
            // User yang masih ada di election lain → cuma di-detach, record tetap.
            $usersToDelete = User::whereIn('id', $allTargetIds)
                ->whereDoesntHave('elections')
                ->whereHas('roles', fn ($q) => $q->where('name', 'pemilih'))
                ->get();

            foreach ($usersToDelete as $user) {
                // Cleanup voter_logs di election lain (user gak punya election sekarang, jadi log nya stranded)
                VoterLog::where('user_id', $user->id)->delete();
                $user->delete();
            }

            $stats['users_deleted'] = $usersToDelete->count();
        });

        $this->audit('destroy_all_voters', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            ...$stats,
        ]);

        $msg = "{$stats['users_deleted']} pemilih dihapus dari sistem.";
        if ($stats['orphans_removed'] > 0 && $stats['attached_removed'] === 0) {
            $msg = "{$stats['orphans_removed']} pemilih orphan dari import sebelumnya berhasil dibersihkan.";
        } elseif ($stats['orphans_removed'] > 0) {
            $msg .= " (termasuk {$stats['orphans_removed']} orphan dari import sebelumnya)";
        }

        return redirect()->route('admin.dpt.index', $election)
            ->with('success', $msg);
    }

    /**
     * Pulihkan orphan pemilih ke election ini (attach balik ke pivot).
     * Berguna kalau sebelumnya pernah detach all dan ingin re-use data tanpa import ulang.
     */
    public function restoreOrphans(Election $election): RedirectResponse
    {
        if ($election->status === 'finished') {
            return redirect()->route('admin.dpt.index', $election)
                ->with('error', 'Tidak bisa modifikasi DPT untuk pemilihan yang sudah selesai.');
        }

        $orphans = User::role('pemilih')->whereDoesntHave('elections')->pluck('id')->toArray();

        if (empty($orphans)) {
            return redirect()->route('admin.dpt.index', $election)
                ->with('error', 'Tidak ada pemilih orphan untuk dipulihkan.');
        }

        // Attach dengan has_voted=false (status reset)
        $payload = [];
        foreach ($orphans as $userId) {
            $payload[$userId] = ['has_voted' => false];
        }
        $election->voters()->syncWithoutDetaching($payload);

        $count = count($orphans);

        $this->audit('restore_orphan_voters', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            'voters_restored' => $count,
        ]);

        return redirect()->route('admin.dpt.index', $election)
            ->with('success', "{$count} pemilih berhasil dipulihkan ke DPT.");
    }

    /**
     * Reset password dan status voting user.
     */
    public function resetUser(Request $request, User $user): RedirectResponse
    {
        // Cari election yang terkait dengan user ini
        $election = $user->elections()->first();

        if (!$election) {
            return back()->with('error', 'User tidak terdaftar di election manapun.');
        }

        $this->importService->resetUser($user, $election);

        $this->audit('reset_user', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'username' => $user->username,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return back()->with('success', "User {$user->name} ({$user->username}) berhasil direset.");
    }
}
