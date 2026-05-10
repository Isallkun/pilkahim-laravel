<?php

namespace App\Services;

use App\Imports\DPTImport;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class DPTImportService
{
    /**
     * Import DPT dari file Excel.
     */
    public function import(UploadedFile $file, Election $election): array
    {
        $import = new DPTImport($election);

        Excel::import($import, $file);

        $failures = $import->failures();

        $errors = [];
        foreach ($failures as $failure) {
            $errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }

        return [
            'success_count' => $import->getSuccessCount(),
            'failure_count' => count($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Reset user: password kembali ke bcrypt(username), hapus voter_logs & votes, reset status.
     */
    public function resetUser(User $user, Election $election): void
    {
        // Reset password ke bcrypt(username)
        $user->update([
            'password' => Hash::make($user->username),
            'has_voted' => false,
            'password_changed_at' => now(),
        ]);

        // Hapus voter_logs untuk user ini di election ini (biar bisa vote ulang)
        \App\Models\VoterLog::where('user_id', $user->id)
            ->where('election_id', $election->id)
            ->delete();

        // Reset has_voted di pivot
        $election->voters()->updateExistingPivot($user->id, [
            'has_voted' => false,
        ]);
    }
}
