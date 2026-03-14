@extends('voting.layouts.admin')
@section('title', 'Edit: ' . $event->title)

@section('content')
    <h1 class="text-2xl font-bold mb-6">Edit Event</h1>

    <form method="POST" action="{{ route('voting.admin.events.update', $event) }}"
        class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 max-w-2xl">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Judul Event *</label>
            <input type="text" name="title" value="{{ old('title', $event->title) }}" class="w-full border p-2 rounded"
                required>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" class="w-full border p-2 rounded h-24">{{ old('description', $event->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Deadline Submission</label>
            <input type="datetime-local" name="submission_deadline"
                value="{{ old('submission_deadline', $event->submission_deadline?->format('Y-m-d\TH:i')) }}"
                class="w-full border p-2 rounded">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
            <a href="{{ route('voting.admin.events.show', $event) }}"
                class="px-6 py-2 border rounded hover:bg-gray-50">Batal</a>
        </div>
    </form>
@endsection
