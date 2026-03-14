@extends('voting.layouts.app')

@section('title', $submission->title)

@section('content')
    <a href="{{ route('voting.gallery', $event->slug) }}"
        class="text-sm text-blue-600 hover:underline mb-4 inline-block">← Kembali ke Gallery</a>

    @php
        $thumbnailUrl = $submission->thumbnail_path
            ? (\Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
                ? asset($submission->thumbnail_path)
                : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path))
            : asset('images/placeholder-ss.png');
    @endphp

    <div class="max-w-4xl">
        <div class="mb-4">
            <span class="text-xs font-medium px-2 py-1 rounded bg-blue-100 text-blue-700 capitalize">
                {{ $submission->concentration }}
            </span>
            <h1 class="text-2xl font-bold mt-2">{{ $submission->title }}</h1>
            <p class="text-gray-500">oleh {{ $submission->submitter?->name ?? 'Peserta' }}</p>
        </div>

        <div class="mb-6">
            <img src="{{ $thumbnailUrl }}"
                alt="{{ $submission->title }}"
                class="w-full max-h-125 object-contain rounded-lg bg-gray-100">
        </div>

        @if($submission->screenshots->count())
            <div class="mb-6">
                <h2 class="font-semibold text-sm text-gray-500 mb-2">Screenshot</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($submission->screenshots as $ss)
                        @php
                            $imageUrl = $ss->image_path
                                ? (\Illuminate\Support\Str::startsWith($ss->image_path, 'images/')
                                    ? asset($ss->image_path)
                                    : \Illuminate\Support\Facades\Storage::url($ss->image_path))
                                : asset('images/placeholder-ss.png');
                        @endphp

                        <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $imageUrl }}"
                                class="w-full h-40 object-cover rounded hover:opacity-90 transition"
                                loading="lazy"
                                alt="Screenshot karya {{ $submission->title }}">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-6">
            <h2 class="font-semibold text-sm text-gray-500 mb-2">Deskripsi</h2>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="whitespace-pre-line">{{ $submission->description }}</p>
            </div>
        </div>

        @if($submission->demo_url)
            <div class="mb-6">
                <h2 class="font-semibold text-sm text-gray-500 mb-2">Demo</h2>
                <a href="{{ $submission->demo_url }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 bg-white rounded-lg px-4 py-3 shadow-sm text-blue-600 hover:text-blue-800 transition break-all">
                    {{ $submission->demo_url }}
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        @endif

        @if($event->isClosed() && $voteCount !== null)
            <div class="mb-6 bg-white rounded-lg p-4 shadow-sm text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $voteCount }}</p>
                <p class="text-sm text-gray-500">total vote</p>
            </div>
        @endif

        <div class="bg-white rounded-lg p-4 shadow-sm">
            @if($event->isVotingOpen())
                @auth
                    @if(isset($userVotes[$submission->concentration]) && (int) $userVotes[$submission->concentration] === $submission->id)
                        <div class="bg-green-50 text-green-700 px-6 py-3 rounded text-center font-medium">
                            Kamu sudah vote karya ini ✓
                        </div>
                    @elseif(isset($userVotes[$submission->concentration]))
                        <div class="bg-gray-100 text-gray-500 px-6 py-3 rounded text-center">
                            Kamu sudah vote di konsentrasi {{ $submission->concentration }}
                        </div>
                    @else
                        <p class="text-center text-gray-400 py-3">[Vote button — aktif di Fase 4]</p>
                    @endif
                @else
                    <a href="{{ route('voting.login') }}"
                        class="block text-center bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition">
                        Login untuk Vote
                    </a>
                @endauth
            @elseif($event->isClosed())
                <p class="text-center text-gray-500 py-3">Voting sudah ditutup.</p>
            @else
                <p class="text-center text-gray-500 py-3">Voting belum dibuka.</p>
            @endif
        </div>
    </div>
@endsection
