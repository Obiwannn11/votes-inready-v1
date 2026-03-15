@extends('voting.layouts.app')

@section('title', $event->title . ' — Gallery')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
        @if ($event->description)
            <p class="text-gray-500 mt-1">{{ $event->description }}</p>
        @endif

        @if ($event->isClosed())
            <a href="{{ route('voting.results', $event->slug) }}"
                class="inline-block mt-3 text-sm bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
                Lihat Hasil Voting
            </a>
        @endif

        @if ($event->isVotingOpen())
            <span class="inline-block mt-2 text-xs font-medium px-3 py-1 rounded bg-green-100 text-green-700">
                Voting Dibuka
            </span>
            @auth
                <span class="text-xs text-gray-500 ml-2">Vote terpakai: {{ $userVoteCount }}/3</span>
            @endauth
        @elseif($event->isClosed())
            <span class="inline-block mt-2 text-xs font-medium px-3 py-1 rounded bg-gray-100 text-gray-600">
                Voting Ditutup — Hasil Final
            </span>
        @endif
    </div>

    <div class="flex gap-2 mb-6 flex-wrap">
        <a href="{{ route('voting.gallery', $event->slug) }}"
            class="px-4 py-2 rounded text-sm transition {{ !$concentration ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Semua
        </a>

        @foreach (['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $key => $label)
            <a href="{{ route('voting.gallery', [$event->slug, 'c' => $key]) }}"
                class="px-4 py-2 rounded text-sm transition {{ $concentration === $key ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($submissions as $sub)
            @php
                $thumbnailUrl = $sub->thumbnail_path
                    ? (\Illuminate\Support\Str::startsWith($sub->thumbnail_path, 'images/')
                        ? asset($sub->thumbnail_path)
                        : \Illuminate\Support\Facades\Storage::url($sub->thumbnail_path))
                    : asset('images/placeholder-ss.png');
            @endphp

            <a href="{{ route('voting.detail', [$event->slug, $sub->id]) }}"
                class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden group">
                <div class="relative">
                    <img src="{{ $thumbnailUrl }}" alt="{{ $sub->title }}"
                        class="w-full h-48 object-cover group-hover:scale-105 transition duration-300" loading="lazy">

                    <span
                        class="absolute top-2 left-2 text-xs font-medium px-2 py-1 rounded bg-white/90 text-gray-700 capitalize">
                        {{ $sub->concentration }}
                    </span>

                    @if (isset($userVotes[$sub->concentration]) && (int) $userVotes[$sub->concentration] === $sub->id)
                        <span class="absolute top-2 right-2 text-xs font-medium px-2 py-1 rounded bg-green-500 text-white">
                            Voted ✓
                        </span>
                    @endif
                </div>

                <div class="p-4">
                    <h3 class="font-semibold group-hover:text-blue-600 transition">{{ $sub->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $sub->submitter?->name ?? 'Peserta' }}</p>

                    @if ($event->isClosed())
                        <p class="text-sm font-medium text-blue-600 mt-2">
                            {{ $voteCounts[$sub->id] ?? 0 }} vote
                        </p>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-full text-center text-gray-400 py-16">
                @if ($concentration)
                    Belum ada karya di konsentrasi ini.
                @else
                    Belum ada karya yang di-approve.
                @endif
            </div>
        @endforelse
    </div>
@endsection
