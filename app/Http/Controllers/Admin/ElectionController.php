<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreElectionRequest;
use App\Http\Requests\Admin\UpdateElectionRequest;
use App\Models\Election;
use App\Traits\Auditable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElectionController extends Controller
{
    use Auditable;
    public function index(): View
    {
        // Eager load count untuk hero card & history rows.
        $elections = Election::withCount([
                'candidates',
                'voters',
                'voters as voted_voters_count' => fn ($q) => $q->where('election_user.has_voted', true),
            ])
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'draft' THEN 2 WHEN 'finished' THEN 3 ELSE 4 END")
            ->orderByDesc('start_date')
            ->get();

        return view('admin.elections.index', compact('elections'));
    }

    public function create(): RedirectResponse
    {
        // Form sudah jadi modal di halaman index — direct visit di-redirect ke sana.
        return redirect()->route('admin.elections.index', ['modal' => 'create']);
    }

    public function store(StoreElectionRequest $request): RedirectResponse
    {
        $election = Election::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
            'result_visibility' => 'private',
        ]);

        $this->audit('create_election', [
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.elections.index')
            ->with('success', 'Election berhasil dibuat.');
    }

    public function edit(Election $election): RedirectResponse
    {
        // Form sudah jadi modal di halaman index — direct visit di-redirect ke sana
        // dan data election di-flash ke old() supaya modal langsung terisi.
        return redirect()->route('admin.elections.index')->withInput([
            '_modal' => 'edit:' . $election->id,
            'name' => $election->name,
            'start_date' => $election->start_date->format('Y-m-d\TH:i'),
            'end_date' => $election->end_date->format('Y-m-d\TH:i'),
            'status' => $election->status,
        ]);
    }

    public function update(UpdateElectionRequest $request, Election $election): RedirectResponse
    {
        $election->update($request->validated());

        $this->audit('update_election', [
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.elections.index')
            ->with('success', 'Election berhasil diperbarui.');
    }

    public function toggleResults(Election $election): RedirectResponse
    {
        if ($election->status !== 'finished') {
            return redirect()->route('admin.elections.index')
                ->with('error', 'Hasil hanya bisa ditoggle ketika election sudah selesai.');
        }

        $newVisibility = $election->result_visibility === 'private' ? 'public' : 'private';

        $election->update([
            'result_visibility' => $newVisibility,
        ]);

        $this->audit('toggle_results', [
            'election_id' => $election->id,
            'election_name' => $election->name,
            'new_visibility' => $newVisibility,
        ]);

        return redirect()->route('admin.elections.index')
            ->with('success', 'Visibilitas hasil berhasil diubah.');
    }
}
