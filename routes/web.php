<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DPTController;
use App\Http\Controllers\Admin\ElectionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicResultController;
use App\Http\Controllers\SaksiDashboardController;
use App\Http\Controllers\VoteController;
use App\Livewire\AdminDashboard;
use App\Models\Election;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Admin/Panitia routes
Route::middleware(['auth', 'role:panitia'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

    // Elections CRUD
    Route::resource('elections', ElectionController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('/elections/{election}/toggle-results', [ElectionController::class, 'toggleResults'])->name('elections.toggle-results');

    // Candidates CRUD (nested under elections)
    Route::resource('elections.candidates', CandidateController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // DPT (Daftar Pemilih Tetap)
    Route::get('/elections/{election}/dpt', [DPTController::class, 'index'])->name('dpt.index');
    Route::get('/elections/{election}/dpt/import', [DPTController::class, 'showImportForm'])->name('dpt.import-form');
    Route::post('/elections/{election}/dpt/import', [DPTController::class, 'import'])->name('dpt.import');
    Route::post('/elections/{election}/dpt', [DPTController::class, 'storeUser'])->name('dpt.store');
    Route::put('/elections/{election}/dpt/{user}', [DPTController::class, 'updateUser'])->name('dpt.update');
    Route::delete('/elections/{election}/dpt/{user}', [DPTController::class, 'destroyUser'])->name('dpt.destroy');
    Route::delete('/elections/{election}/dpt', [DPTController::class, 'destroyAllUsers'])->name('dpt.destroy-all');
    Route::post('/elections/{election}/dpt/restore-orphans', [DPTController::class, 'restoreOrphans'])->name('dpt.restore-orphans');
    Route::post('/users/{user}/reset', [DPTController::class, 'resetUser'])->name('users.reset');

    // Placeholder routes
    Route::get('/voters', function () { return redirect()->route('admin.elections.index'); })->name('voters.index');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/elections/{election}/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/elections/{election}/reports/result', [ReportController::class, 'result'])->name('reports.result');

    // Settings (per-election config + danger zone)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/elections/{election}/settings/countdown', [SettingsController::class, 'updateCountdown'])->name('settings.countdown');
    Route::patch('/elections/{election}/settings/result-visibility', [SettingsController::class, 'updateResultVisibility'])->name('settings.result-visibility');
    Route::patch('/elections/{election}/settings/close-voting', [SettingsController::class, 'closeVoting'])->name('settings.close-voting');
    Route::delete('/elections/{election}/settings/votes', [SettingsController::class, 'destroyVotes'])->name('settings.destroy-votes');
});

// Pemilih routes
Route::middleware(['auth', 'role:pemilih'])->group(function () {
    Route::get('/vote/{election}', [VoteController::class, 'showBallot'])->name('vote.ballot');
    Route::post('/vote/{election}/submit', [VoteController::class, 'submitVote'])->name('vote.submit');
});

// Saksi routes
Route::middleware(['auth', 'role:saksi'])->prefix('saksi')->name('saksi.')->group(function () {
    Route::get('/dashboard/{election?}', [SaksiDashboardController::class, 'show'])->name('dashboard');
});

// Public results (no auth required, checks result_visibility)
Route::get('/results/{election}', [PublicResultController::class, 'show'])->name('results.public');
Route::get('/results/{election}/live', [PublicResultController::class, 'live'])->name('results.public.live');

// Home — Landing page for guests, redirect for authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('panitia')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('saksi')) return redirect()->route('saksi.dashboard');

        // Pemilih: cek apakah ada election aktif dan belum voting
        $activeElection = Election::where('status', 'active')->first();
        if ($activeElection) {
            // Cek apakah sudah voting
            $hasVoted = \DB::table('election_user')
                ->where('election_id', $activeElection->id)
                ->where('user_id', $user->id)
                ->where('has_voted', true)
                ->exists();

            if ($hasVoted) {
                // Sudah voting — tampilkan halaman "sudah voting"
                return view('vote.already-voted', ['election' => $activeElection]);
            }

            return redirect()->route('vote.ballot', $activeElection);
        }

        // Tidak ada election aktif
        return view('vote.no-election');
    }

    // Guest: show landing page with active election candidates
    $election = Election::where('status', 'active')->with('candidates')->first();
    $candidates = $election ? $election->candidates : collect();

    return view('landing', [
        'election' => $election,
        'candidates' => $candidates,
    ]);
})->name('home');
