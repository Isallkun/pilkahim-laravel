<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportDPTRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,csv,xls', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File DPT wajib diunggah.',
            'file.file' => 'Upload harus berupa file.',
            'file.mimes' => 'Format file harus xlsx, csv, atau xls.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
