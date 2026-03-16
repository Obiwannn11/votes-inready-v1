@extends('voting.layouts.admin')
@section('title', 'Buat Event')

@section('content')
    <div class="mb-8">
        <h1 class="section-title mb-2">Buat Event Baru</h1>
        <p class="section-subtitle">Isi form di bawah untuk membuat event voting baru</p>
    </div>

    <form method="POST" action="{{ route('voting.admin.events.store') }}"
        class="card bg-surface p-6 shadow-[6px_6px_0px_0px_var(--color-ink)] max-w-2xl border-2 border-ink">
        @csrf

        <h2 class="font-display font-black text-xl mb-6 pl-3 border-l-4 border-primary-blue uppercase">Detail Event</h2>

        <div class="form-group mb-6">
            <x-label for="title" required>Judul Event</x-label>
            <x-input type="text" name="title" id="title" value="{{ old('title') }}" required
                placeholder="Masukkan judul event" :error="$errors->has('title')"
                class="{{ $errors->has('title') ? 'error' : '' }}" />
            @error('title')
                <p class="form-helper error mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group mb-6">
            <x-label for="description">Deskripsi</x-label>
            <textarea name="description" id="description" rows="4"
                class="w-full border-2 border-ink bg-surface p-3 font-body text-ink focus:outline-none focus:ring-0 focus:shadow-[4px_4px_0px_0px_var(--color-ink)] transition-shadow"
                placeholder="Deskripsi singkat tentang event...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group mb-8">
            <x-label for="submission_deadline">Deadline Submission</x-label>
            <input type="datetime-local" name="submission_deadline" id="submission_deadline"
                value="{{ old('submission_deadline') }}"
                class="w-full border-2 border-ink bg-surface px-4 py-3 text-sm font-body transition-all duration-200 outline-none focus:shadow-[4px_4px_0px_0px_var(--color-ink)]">
            <p class="form-helper mt-1 text-ink/60">Opsional. Kosongkan jika tidak ada deadline.</p>
        </div>

        <hr class="border-t-2 border-ink my-8">

        <div class="flex gap-3">
            <x-button type="submit" variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Simpan
            </x-button>
            <x-button variant="outline" href="{{ route('voting.admin.events.index') }}">Batal</x-button>
        </div>
    </form>
@endsection
