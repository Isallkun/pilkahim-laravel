<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'username' => ['required', 'string', 'size:3', 'regex:/^[0-9]+$/', Rule::unique('users', 'username')->ignore($userId)],
            'name' => ['required', 'string', 'max:255'],
            // Password opsional — kalau kosong, password lama dipakai
            'password' => ['nullable', 'string', 'min:6'],
            'angkatan' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:L,P'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'NIM/Username wajib diisi.',
            'username.size' => 'Username harus 3 digit.',
            'username.regex' => 'Username harus angka (0-9).',
            'username.unique' => 'Username sudah terdaftar.',
            'name.required' => 'Nama wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'gender.in' => 'Gender harus L atau P.',
        ];
    }
}
