<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'size:3', 'regex:/^[0-9]+$/', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
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
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'angkatan.required' => 'Angkatan wajib diisi.',
            'gender.in' => 'Gender harus L atau P.',
        ];
    }
}
