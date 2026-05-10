<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitVoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'candidate_id' => [
                'required',
                'integer',
                Rule::exists('candidates', 'id')->where(function ($query) {
                    // Ensure candidate belongs to the election in the route
                    $query->where('election_id', $this->route('election')->id);
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'candidate_id.required' => 'Silakan pilih salah satu kandidat.',
            'candidate_id.exists' => 'Kandidat yang dipilih tidak valid untuk pemilihan ini.',
        ];
    }
}
