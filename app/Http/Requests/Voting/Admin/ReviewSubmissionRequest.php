<?php

namespace App\Http\Requests\Voting\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'required_if:status,rejected|nullable|string|max:3000',
        ];
    }

    public function messages(): array
    {
        return [
            'admin_notes.required_if' => 'Alasan reject wajib diisi.',
        ];
    }
}
