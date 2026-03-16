@extends('voting.layouts.admin')
@section('title', $submission->title)

@section('content')
    {{-- Back link --}}
    <a href="{{ route('voting.admin.submissions', $submission->event) }}"
        class="inline-flex items-center gap-1 font-display font-bold text-xs uppercase tracking-widest text-ink/50 hover:text-primary-blue transition-colors mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Kembali ke List
    </a>

    <div class="card bg-surface p-6 md:p-8 max-w-3xl border-2 border-ink shadow-[6px_6px_0px_0px_var(--color-ink)]">
        {{-- Header --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="section-title mb-2">{{ $submission->title }}</h1>
                <p class="font-body text-sm text-ink/60">
                    Oleh: <strong class="text-ink">{{ $submission->submitter->name ?? 'Unknown' }}</strong>
                </p>
            </div>
            <x-badge :type="$submission->status" :pill="true">{{ $submission->status }}</x-badge>
        </div>

        {{-- Thumbnail --}}
        @if ($submission->thumbnail_path)
            @php
                $thumbnailUrl = \Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
                    ? asset($submission->thumbnail_path)
                    : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path);
            @endphp
            <img src="{{ $thumbnailUrl }}"
                class="w-full h-64 object-cover border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] mb-6 bg-canvas"
                alt="Thumbnail karya {{ $submission->title }}" loading="lazy">
        @endif

        {{-- Description --}}
        <div class="mb-6">
            <h3 class="font-display font-black text-lg mb-3 pl-3 border-l-4 border-primary-blue uppercase">Deskripsi</h3>
            <p class="font-body text-sm text-ink/80 whitespace-pre-wrap leading-relaxed">{{ $submission->description }}</p>
        </div>

        {{-- Links --}}
        @if ($submission->demo_url || $submission->github_url)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                @if ($submission->demo_url)
                    <a href="{{ $submission->demo_url }}" target="_blank" rel="noopener noreferrer"
                        class="block p-4 border-2 border-ink bg-surface text-center hover:bg-muted transition-colors shadow-[4px_4px_0px_0px_var(--color-ink)] hover:-translate-y-1 transition-transform duration-200">
                        <span class="block font-display font-bold uppercase text-sm text-primary-blue tracking-wide">Demo URL</span>
                        <span class="font-body text-xs text-ink/50 truncate block mt-1">{{ $submission->demo_url }}</span>
                    </a>
                @endif
                @if ($submission->github_url)
                    <a href="{{ $submission->github_url }}" target="_blank" rel="noopener noreferrer"
                        class="block p-4 border-2 border-ink bg-surface text-center hover:bg-muted transition-colors shadow-[4px_4px_0px_0px_var(--color-ink)] hover:-translate-y-1 transition-transform duration-200">
                        <span class="block font-display font-bold uppercase text-sm text-ink tracking-wide">GitHub</span>
                        <span class="font-body text-xs text-ink/50 truncate block mt-1">{{ $submission->github_url }}</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- Screenshots --}}
        @if ($submission->screenshots->count() > 0)
            <div class="mb-8">
                <h3 class="font-display font-black text-lg mb-3 pl-3 border-l-4 border-primary-red uppercase">Screenshots</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($submission->screenshots as $ss)
                        @php
                            $screenshotUrl = \Illuminate\Support\Str::startsWith($ss->image_path, 'images/')
                                ? asset($ss->image_path)
                                : \Illuminate\Support\Facades\Storage::url($ss->image_path);
                        @endphp
                        <div class="border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] aspect-[4/3] bg-canvas overflow-hidden">
                            <img src="{{ $screenshotUrl }}" class="w-full h-full object-cover"
                                alt="Screenshot karya {{ $submission->title }}" loading="lazy">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Admin Notes --}}
        @if ($submission->admin_notes)
            <div class="mb-6 border-2 border-ink bg-primary-yellow/10 p-4 shadow-[4px_4px_0px_0px_var(--color-ink)]">
                <h3 class="font-display font-bold text-xs uppercase tracking-widest text-ink mb-2">Catatan Admin Saat Ini</h3>
                <p class="font-body text-sm text-ink/80 whitespace-pre-wrap">{{ $submission->admin_notes }}</p>
            </div>
        @endif

        <hr class="border-t-2 border-ink my-8">

        {{-- Review Actions --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Approve --}}
            <form method="POST" action="{{ route('voting.admin.submissions.review', $submission) }}"
                class="border-2 border-ink p-5 bg-surface shadow-[4px_4px_0px_0px_var(--color-ink)]">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="approved">

                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-success text-surface border-2 border-ink flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h3 class="font-display font-black text-lg uppercase">Approve</h3>
                </div>
                <p class="font-body text-xs text-ink/60 mb-4">Status akan menjadi approved dan user tidak bisa mengubah karya lagi.</p>

                <x-button type="submit" variant="primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Approve Submission
                </x-button>
            </form>

            {{-- Reject --}}
            <form method="POST" action="{{ route('voting.admin.submissions.review', $submission) }}"
                class="border-2 border-ink p-5 bg-surface shadow-[4px_4px_0px_0px_var(--color-ink)]">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="rejected">

                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-primary-red text-surface border-2 border-ink flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                    <h3 class="font-display font-black text-lg uppercase">Reject</h3>
                </div>

                <div class="form-group mb-4">
                    <x-label for="admin_notes" required>Alasan Reject</x-label>
                    <textarea id="admin_notes" name="admin_notes" rows="4" required
                        class="w-full border-2 border-ink bg-surface p-3 font-body text-sm text-ink focus:outline-none focus:ring-0 focus:shadow-[4px_4px_0px_0px_var(--color-ink)] transition-shadow {{ $errors->has('admin_notes') ? 'border-primary-red' : '' }}"
                        placeholder="Jelaskan alasan penolakan...">{{ old('admin_notes', $submission->admin_notes) }}</textarea>
                    @error('admin_notes')
                        <p class="form-helper error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-button type="submit" variant="danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="square" stroke-linejoin="miter">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Reject Submission
                </x-button>
            </form>
        </div>
    </div>
@endsection
