@extends('voting.layouts.admin')
@section('title', 'Buat Event')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Buat Event Baru</h1>

    <form method="POST" action="{{ route('voting.admin.events.store') }}"
        class="bg-white rounded-lg shadow-sm p-6 max-w-2xl border border-gray-100">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Judul Event *</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border p-2 rounded" required>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" class="w-full border p-2 rounded h-24">{{ old('description') }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Deadline Submission</label>
            <input type="datetime-local" name="submission_deadline" value="{{ old('submission_deadline') }}"
                class="w-full border p-2 rounded">
            <p class="text-gray-400 text-xs mt-1">Opsional. Kosongkan jika tidak ada deadline.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Simpan</button>
            <a href="{{ route('voting.admin.events.index') }}" class="px-6 py-2 border rounded hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection
