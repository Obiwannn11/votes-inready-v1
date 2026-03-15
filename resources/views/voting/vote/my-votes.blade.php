@extends('voting.layouts.app')

@section('title', 'Vote Saya')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-2">Vote Saya</h1>
        <p class="text-gray-500 mb-6">{{ $event->title }} · {{ $votes->count() }}/3 vote terpakai</p>

        @forelse($votes as $vote)
            @php
                $thumbnailUrl =
                    $vote->submission && $vote->submission->thumbnail_path
                        ? (\Illuminate\Support\Str::startsWith($vote->submission->thumbnail_path, 'images/')
                            ? asset($vote->submission->thumbnail_path)
                            : \Illuminate\Support\Facades\Storage::url($vote->submission->thumbnail_path))
                        : asset('images/placeholder-ss.png');
            @endphp

            <div class="bg-white rounded-lg shadow-sm p-4 mb-3 flex gap-4 flex-col sm:flex-row">
                <img src="{{ $thumbnailUrl }}" class="w-16 h-16 object-cover rounded" alt="Thumbnail karya" loading="lazy">
                <div>
                    <a href="{{ route('voting.detail', [$event->slug, $vote->submission->id]) }}"
                        class="font-semibold hover:text-blue-600">
                        {{ $vote->submission->title }}
                    </a>
                    <p class="text-sm text-gray-500">
                        {{ $vote->submission->submitter?->name ?? 'Peserta' }} ·
                        <span class="font-medium">{{ $vote->concentration }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Divote {{ $vote->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-12 bg-white rounded-lg shadow-sm">
                Kamu belum memberikan vote.
                <br>
                <a href="{{ route('voting.gallery', $event->slug) }}"
                    class="text-blue-600 hover:underline mt-2 inline-block">
                    Browse gallery →
                </a>
            </div>
        @endforelse

        @if ($votes->count() < 3)
            <p class="text-center mt-4">
                <a href="{{ route('voting.gallery', $event->slug) }}" class="text-sm text-blue-600 hover:underline">
                    Kamu masih punya {{ 3 - $votes->count() }} vote lagi →
                </a>
            </p>
        @endif
    </div>
@endsection
