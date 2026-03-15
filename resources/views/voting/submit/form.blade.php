@extends('voting.layouts.app')
@section('title', 'Submit Karya — ' . $event->title)

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Submit Karya</h1>
        <p class="text-gray-500 mb-6">{{ $event->title }}</p>

        @if ($event->submission_deadline)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-6 text-sm">
                Deadline: <strong>{{ $event->submission_deadline->format('d M Y, H:i') }} WITA</strong>
            </div>
        @endif

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('voting.submit.store', $event->slug) }}" enctype="multipart/form-data"
            class="bg-white rounded-lg shadow-sm p-6">
            @csrf

            {{-- Data Author --}}
            <h2 class="font-semibold text-lg mb-4">Informasi Tim/Author</h2>
            <div class="mb-6 p-4 bg-gray-50 rounded border">
                <p class="text-sm"><strong>Nama:</strong> {{ auth()->user()->name }}</p>
                <p class="text-sm text-gray-500"><strong>Email:</strong> {{ auth()->user()->email }}</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Konsentrasi *</label>
                <div class="flex gap-4">
                    @foreach (['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $val => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="concentration" value="{{ $val }}"
                                {{ old('concentration') === $val ? 'checked' : '' }} required class="text-blue-600">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('concentration')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <hr class="my-6">

            {{-- Data Karya --}}
            <h2 class="font-semibold text-lg mb-4">Data Karya</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Judul Karya *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full border rounded px-3 py-2 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Deskripsi Karya *</label>
                <textarea name="description" rows="5" required
                    class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror"
                    placeholder="Jelaskan karya kamu: apa yang dibuat, teknologi yang dipakai, dsb...">{{ old('description') }}</textarea>
                <p class="text-gray-400 text-xs mt-1">Maksimal 5000 karakter</p>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4" x-data="{ preview: null }">
                <label class="block text-sm font-medium mb-1">Thumbnail Karya * <span class="text-gray-400 font-normal">(max
                        2MB)</span></label>
                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" required
                    @change="preview = URL.createObjectURL($event.target.files[0])"
                    class="w-full border rounded px-3 py-2 text-sm @error('thumbnail') border-red-500 @enderror">
                <img x-show="preview" :src="preview" class="mt-2 max-h-48 rounded" x-cloak
                    alt="Preview thumbnail karya">
                @error('thumbnail')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Screenshot Tambahan <span
                        class="text-gray-400 font-normal">(max 5 file, masing-masing max 2MB)</span></label>
                <input type="file" name="screenshots[]" accept="image/jpeg,image/png,image/webp" multiple
                    class="w-full border rounded px-3 py-2 text-sm">
                @error('screenshots')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('screenshots.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Link Demo/Live <span
                            class="text-gray-400 font-normal">(opsional)</span></label>
                    <input type="url" name="demo_url" value="{{ old('demo_url') }}" placeholder="https://..."
                        class="w-full border rounded px-3 py-2">
                    @error('demo_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Link Repository <span
                            class="text-gray-400 font-normal">(opsional)</span></label>
                    <input type="url" name="github_url" value="{{ old('github_url') }}"
                        placeholder="https://github.com/..." class="w-full border rounded px-3 py-2">
                    @error('github_url')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 font-medium">
                Submit Karya
            </button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-4 pb-12">
            Melihat status karya? <a href="{{ route('voting.submit.status', [$event->slug]) }}"
                class="text-blue-600 hover:underline">Cek di sini →</a>
        </p>
    </div>
@endsection
