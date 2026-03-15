@extends('voting.layouts.app')

@section('title', 'Vote Saya')

@section('content')
    <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-display font-black uppercase tracking-tight text-ink mb-2">Vote Saya</h1>
        <p class="font-body text-grey font-medium tracking-wide uppercase text-sm sm:text-base mb-8">{{ $event->title }} ·
            <span class="text-primary-blue font-bold">{{ $votes->count() }}/3</span> vote terpakai</p>

        <div class="space-y-4">
            @forelse($votes as $vote)
                @php
                    $thumbnailUrl =
                        $vote->submission && $vote->submission->thumbnail_path
                            ? (\Illuminate\Support\Str::startsWith($vote->submission->thumbnail_path, 'images/')
                                ? asset($vote->submission->thumbnail_path)
                                : \Illuminate\Support\Facades\Storage::url($vote->submission->thumbnail_path))
                            : asset('images/placeholder-ss.png');
                @endphp

                <x-card padding="p-0" border="thick"
                    class="flex flex-col sm:flex-row shadow-[4px_4px_0px_0px_var(--color-ink)]">
                    <div class="sm:w-48 h-48 sm:h-auto shrink-0 border-b-4 sm:border-b-0 sm:border-r-4 border-ink">
                        <img src="{{ $thumbnailUrl }}" class="w-full h-full object-cover grayscale-[20%]"
                            alt="Thumbnail karya" loading="lazy">
                    </div>
                    <div class="p-6 flex flex-col justify-center flex-1">
                        <a href="{{ route('voting.detail', [$event->slug, $vote->submission->id]) }}"
                            class="font-display font-bold text-2xl uppercase tracking-tight text-ink hover:text-primary-blue transition-colors mb-2">
                            {{ $vote->submission->title }}
                        </a>
                        <p class="font-body text-sm font-medium text-grey uppercase tracking-widest mb-4">
                            {{ $vote->submission->submitter?->name ?? 'Peserta' }}
                        </p>
                        <div class="flex items-center justify-between mt-auto">
                            <x-badge type="{{ $vote->concentration }}">
                                {{ $vote->concentration }}
                            </x-badge>
                            <p class="text-xs font-body font-bold text-muted-foreground uppercase">Divote
                                {{ $vote->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card padding="p-12" border="thick" class="text-center shadow-[6px_6px_0px_0px_var(--color-ink)]">
                    <p class="font-display font-black text-xl text-ink uppercase tracking-widest mb-6">Kamu belum memberikan
                        vote.</p>
                    <x-button variant="primary" href="{{ route('voting.gallery', $event->slug) }}">
                        Browse gallery →
                    </x-button>
                </x-card>
            @endforelse
        </div>

        @if ($votes->count() < 3)
            <div class="text-center mt-12">
                <a href="{{ route('voting.gallery', $event->slug) }}"
                    class="font-display font-bold text-sm bg-ink text-surface px-6 py-3 uppercase tracking-widest hover:bg-primary-blue transition-colors">
                    Kamu masih punya {{ 3 - $votes->count() }} vote lagi →
                </a>
            </div>
        @endif
    </div>
@endsection
