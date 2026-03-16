@extends('voting.layouts.admin')
@section('title', $submission->title)

@section('content')
    <a href="{{ route('voting.admin.submissions', $submission->event) }}"
        class="text-sm text-gray-500 hover:text-blue-600 mb-4 inline-block">← Kembali ke List</a>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 max-w-3xl">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $submission->title }}</h1>
                <p class="text-gray-500">Oleh: {{ $submission->submitter->name ?? 'Unknown' }}</p>
            </div>
            <span
                class="px-3 py-1 rounded text-sm {{ $submission->status === 'approved' ? 'bg-green-100 text-green-700' : ($submission->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                {{ strtoupper($submission->status) }}
            </span>
        </div>

        @if ($submission->thumbnail_path)
            @php
                $thumbnailUrl = \Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
                    ? asset($submission->thumbnail_path)
                    : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path);
            @endphp
            <img src="{{ $thumbnailUrl }}" class="w-full h-64 object-cover rounded mb-6 bg-gray-100"
                alt="Thumbnail karya {{ $submission->title }}" loading="lazy">
        @endif

        <div class="mb-6">
            <h3 class="font-bold text-lg mb-2">Deskripsi</h3>
            <p class="text-gray-700 whitespace-pre-wrap">{{ $submission->description }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8">
            @if ($submission->demo_url)
                <a href="{{ $submission->demo_url }}" target="_blank" rel="noopener noreferrer"
                    class="block p-4 border rounded text-center hover:bg-gray-50">
                    <span class="block font-bold text-blue-600">Demo URL</span>
                    <span class="text-sm text-gray-500 truncate">{{ $submission->demo_url }}</span>
                </a>
            @endif

            @if ($submission->github_url)
                <a href="{{ $submission->github_url }}" target="_blank" rel="noopener noreferrer"
                    class="block p-4 border rounded text-center hover:bg-gray-50">
                    <span class="block font-bold text-gray-800">GitHub</span>
                    <span class="text-sm text-gray-500 truncate">{{ $submission->github_url }}</span>
                </a>
            @endif
        </div>

        @if ($submission->screenshots->count() > 0)
            <div class="mb-8">
                <h3 class="font-bold text-lg mb-3">Screenshots</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($submission->screenshots as $ss)
                        @php
                            $screenshotUrl = \Illuminate\Support\Str::startsWith($ss->image_path, 'images/')
                                ? asset($ss->image_path)
                                : \Illuminate\Support\Facades\Storage::url($ss->image_path);
                        @endphp
                        <img src="{{ $screenshotUrl }}" class="w-full h-32 object-cover rounded bg-gray-100"
                            alt="Screenshot karya {{ $submission->title }}" loading="lazy">
                    @endforeach
                </div>
            </div>
        @endif

        @if ($submission->admin_notes)
            <div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4">
                <h3 class="font-bold text-sm uppercase tracking-wide text-gray-700 mb-1">Catatan Admin Saat Ini</h3>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $submission->admin_notes }}</p>
            </div>
        @endif

        <div class="border-t pt-6 grid gap-4 md:grid-cols-2">
            <form method="POST" action="{{ route('voting.admin.submissions.review', $submission) }}"
                class="border rounded p-4">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="approved">

                <h3 class="font-semibold mb-2">Approve Submission</h3>
                <p class="text-sm text-gray-500 mb-4">Status akan menjadi approved dan user tidak bisa mengubah karya lagi.</p>

                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Approve Submission
                </button>
            </form>

            <form method="POST" action="{{ route('voting.admin.submissions.review', $submission) }}"
                class="border rounded p-4">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="rejected">

                <h3 class="font-semibold mb-2">Reject Submission</h3>
                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">Alasan Reject</label>
                <textarea id="admin_notes" name="admin_notes" rows="4" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-red-200">{{ old('admin_notes', $submission->admin_notes) }}</textarea>
                @error('admin_notes')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror

                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 mt-3">
                    Reject Submission
                </button>
            </form>
        </div>
    </div>
@endsection
