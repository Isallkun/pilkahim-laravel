<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'visi' => ['required', 'string'],
            'misi' => ['required', 'string'],
            'video_url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
