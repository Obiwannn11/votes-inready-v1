@extends('voting.layouts.app')

@section('title', 'Hasil Voting - ' . $event->title)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Hasil Voting</h1>
        <p class="text-gray-500">{{ $event->title }}</p>

        @if ($event->voting_closed_at)
            <p class="text-xs text-gray-400 mt-1">
                Voting ditutup: {{ $event->voting_closed_at->format('d M Y, H:i') }} WITA
            </p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $totalVoters }}</p>
            <p class="text-sm text-gray-500">total voter</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $totalVotes }}</p>
            <p class="text-sm text-gray-500">total vote</p>
        </div>
    </div>

    @php
        $concentrationLabels = ['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'];
        $hasAnyResults = collect($concentrationLabels)->contains(function ($label, $key) use ($results) {
            return isset($results[$key]) && $results[$key]->count() > 0;
        });
    @endphp

    @foreach ($concentrationLabels as $key => $label)
        @if (isset($results[$key]) && $results[$key]->count() > 0)
            <section class="mb-8">
                <h2 class="text-xl font-bold mb-4">{{ $label }}</h2>

                @foreach ($results[$key] as $index => $submission)
                    @php
                        $thumbnailUrl = $submission->thumbnail_path
                            ? (\Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
                                ? asset($submission->thumbnail_path)
                                : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path))
                            : asset('images/placeholder-ss.png');
                    @endphp

                    <article
                        class="bg-white rounded-lg shadow-sm p-4 mb-3 flex gap-4 items-center {{ $index === 0 ? 'ring-2 ring-yellow-400 bg-yellow-50' : '' }}">
                        <div
                            class="w-10 h-10 flex items-center justify-center rounded-full font-bold text-lg {{ $index === 0 ? 'bg-yellow-400 text-white' : 'bg-gray-100 text-gray-500' }}">
                            {{ $index + 1 }}
                        </div>

                        <img src="{{ $thumbnailUrl }}" alt="Thumbnail karya {{ $submission->title }}"
                            class="w-16 h-16 object-cover rounded" loading="lazy">

                        <div class="flex-1 min-w-0">
                            <a href="{{ route('voting.detail', [$event->slug, $submission->id]) }}"
                                class="font-semibold hover:text-blue-600 wrap-break-word">
                                {{ $submission->title }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $submission->submitter?->name ?? 'Peserta' }}</p>
                            @if ($index === 0)
                                <span
                                    class="inline-flex mt-2 text-xs font-medium px-2 py-1 rounded bg-yellow-200 text-yellow-800">
                                    Juara #1
                                </span>
                            @endif
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-2xl font-bold {{ $index === 0 ? 'text-yellow-600' : 'text-gray-700' }}">
                                {{ $submission->votes_count }}
                            </p>
                            <p class="text-xs text-gray-400">vote</p>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    @endforeach

    @if (!$hasAnyResults)
        <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500 mb-8">
            Belum ada karya approved pada event ini, jadi hasil voting belum bisa ditampilkan.
        </div>
    @endif

    <p class="text-center mt-8">
        <a href="{{ route('voting.gallery', $event->slug) }}" class="text-sm text-blue-600 hover:underline">
            Kembali ke Gallery
        </a>
    </p>
@endsection
