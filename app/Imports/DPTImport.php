<?php

namespace App\Imports;

use App\Models\Election;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DPTImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    use \Maatwebsite\Excel\Concerns\SkipsFailures;

    private Election $election;
    private int $successCount = 0;

    public function __construct(Election $election)
    {
        $this->election = $election;

        // Extend time limit for large imports (bcrypt is slow)
        set_time_limit(300);
    }

    /**
     * Map row ke User model.
     */
    public function model(array $row): ?User
    {
        // Skip row yang username-nya kosong atau bukan angka (summary rows: TOTAL, PP, BD, dll)
        $rawUsername = trim($row['username'] ?? '');
        if (empty($rawUsername) || !is_numeric($rawUsername)) {
            return null;
        }

        // Handle heading row variation: "P/L" becomes "p_l" or "pl"
        $gender = $row['p_l'] ?? $row['pl'] ?? null;
        $username = str_pad($rawUsername, 3, '0', STR_PAD_LEFT);

        // Password: kalau kosong, gunakan username sebagai default password
        $rawPassword = trim($row['password'] ?? '');
        $password = !empty($rawPassword) ? $rawPassword : $username;

        // Gender: kalau kosong, set null
        $genderValue = null;
        if ($gender && in_array(strtoupper(trim($gender)), ['L', 'P'])) {
            $genderValue = strtoupper(trim($gender));
        }

        $user = User::create([
            'username' => $username,
            'name' => trim($row['nama'] ?? ''),
            'password' => Hash::make($password, ['rounds' => 10]),
            'angkatan' => trim($row['angkatan'] ?? ''),
            'gender' => $genderValue,
            'has_voted' => false,
            'password_changed_at' => now(),
        ]);

        // Assign role pemilih
        $user->assignRole('pemilih');

        // Attach ke election via pivot
        $this->election->voters()->attach($user->id, [
            'has_voted' => false,
            'created_at' => now(),
        ]);

        $this->successCount++;

        return null;
    }

    /**
     * Validation rules per row.
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'unique:users,username'],
            'nama' => ['required'],
            'angkatan' => ['required'],
            // Password dan P/L sekarang optional
            'password' => ['nullable'],
            'p_l' => ['nullable', Rule::in(['L', 'P', 'l', 'p', ''])],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'p_l.in' => 'Gender harus L atau P.',
        ];
    }

    /**
     * Batch size for inserts.
     */
    public function batchSize(): int
    {
        return 50;
    }

    /**
     * Chunk size for reading (reduces memory usage).
     */
    public function chunkSize(): int
    {
        return 50;
    }

    /**
     * Get jumlah data yang berhasil diimport.
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }
}
