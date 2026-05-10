<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Election;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole('panitia')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->hasRole('saksi')) {
            return redirect()->intended(route('saksi.dashboard'));
        }

        // Default: pemilih → arahkan ke ballot pemilihan aktif
        $activeElection = Election::where('status', 'active')->first();
        if ($activeElection) {
            return redirect()->intended(route('vote.ballot', $activeElection));
        }

        // Tidak ada pemilihan aktif → balik ke home (yang akan menampilkan landing/empty state)
        return redirect()->route('home');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke landing — guest yang baru logout langsung lihat halaman utama,
        // bukan layar login kosong. Lebih ramah dan konsisten dengan flow akhir voting.
        return redirect()->route('home');
    }
}
