<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCandidateRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Traits\Auditable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CandidateController extends Controller
{
    use Auditable;
    public function index(Election $election): View
    {
        $candidates = $election->candidates()->orderBy('sort_order')->get();

        return view('admin.candidates.index', compact('election', 'candidates'));
    }

    public function create(Election $election): RedirectResponse
    {
        // Form sudah jadi modal di halaman index — direct visit di-redirect ke sana.
        return redirect()->route('admin.elections.candidates.index', [$election, 'modal' => 'create']);
    }

    public function store(StoreCandidateRequest $request, Election $election): RedirectResponse
    {
        $data = $request->validated();
        $data['election_id'] = $election->id;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate = Candidate::create($data);

        $this->audit('create_candidate', [
            'candidate_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.elections.candidates.index', $election)
            ->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function edit(Election $election, Candidate $candidate): RedirectResponse
    {
        // Form sudah jadi modal di halaman index — direct visit di-redirect ke sana
        // dan data candidate di-flash ke old() supaya modal langsung terisi.
        return redirect()->route('admin.elections.candidates.index', $election)->withInput([
            '_modal' => 'edit:' . $candidate->id,
            'name' => $candidate->name,
            'visi' => $candidate->visi,
            'misi' => $candidate->misi,
            'video_url' => $candidate->video_url,
            'sort_order' => $candidate->sort_order,
        ]);
    }

    public function update(StoreCandidateRequest $request, Election $election, Candidate $candidate): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate->update($data);

        $this->audit('update_candidate', [
            'candidate_id' => $candidate->id,
            'candidate_name' => $candidate->name,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.elections.candidates.index', $election)
            ->with('success', 'Kandidat berhasil diperbarui.');
    }

    public function destroy(Election $election, Candidate $candidate): RedirectResponse
    {
        if ($election->status !== 'draft') {
            return redirect()->route('admin.elections.candidates.index', $election)
                ->with('error', 'Kandidat hanya bisa dihapus ketika election berstatus draft.');
        }

        $candidateName = $candidate->name;
        $candidate->delete();

        $this->audit('delete_candidate', [
            'candidate_id' => $candidate->id,
            'candidate_name' => $candidateName,
            'election_id' => $election->id,
            'election_name' => $election->name,
        ]);

        return redirect()->route('admin.elections.candidates.index', $election)
            ->with('success', 'Kandidat berhasil dihapus.');
    }
}
