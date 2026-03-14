<?php

namespace App\Http\Requests\Voting\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
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
            'notes' => 'nullable|string', // Optional if we want to add notes feature later
        ];
    }
}
