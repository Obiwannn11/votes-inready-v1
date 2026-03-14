<?php

namespace App\Http\Requests\Voting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitKaryaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'member';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'concentration'   => ['required', 'in:website,design,mobile'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['required', 'string', 'max:5000'],
            'thumbnail'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'screenshots'     => ['nullable', 'array', 'max:5'],
            'screenshots.*'   => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'demo_url'        => ['nullable', 'url', 'max:500'],
            'github_url'      => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'thumbnail.required'  => 'Thumbnail wajib diupload.',
            'thumbnail.image'     => 'Thumbnail harus berupa gambar.',
            'thumbnail.max'       => 'Thumbnail maksimal 2MB.',
            'screenshots.max'     => 'Maksimal 5 screenshot.',
            'screenshots.*.max'   => 'Setiap screenshot maksimal 2MB.',
            'description.max'     => 'Deskripsi maksimal 5000 karakter.',
        ];
    }
}
