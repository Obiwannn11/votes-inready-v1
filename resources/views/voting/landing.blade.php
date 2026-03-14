@extends('voting.layouts.app')

@section('title', 'Inready VOTES — Voting On Talent Excellence & Showcase')

@section('content')
    <div class="text-center py-12">
        <h1 class="text-3xl font-bold mb-2">Inready VOTES</h1>
        <p class="text-gray-500 mb-8">Voting On Talent Excellence & Showcase</p>

        @forelse($events as $event)
            <a href="{{ route('voting.gallery', $event->slug) }}"
                class="block max-w-md mx-auto bg-white rounded-lg shadow-sm p-6 mb-3 hover:shadow-md transition text-left">
                <div class="flex justify-between items-start gap-3">
                    <h2 class="font-semibold text-lg">{{ $event->title }}</h2>
                    <span class="text-xs font-medium px-2 py-1 rounded whitespace-nowrap
                        {{ $event->status === 'voting_open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $event->isVotingOpen() ? 'VOTE SEKARANG' : 'SELESAI' }}
                    </span>
                </div>

                @if($event->description)
                    <p class="text-sm text-gray-500 mt-2">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                @endif
            </a>
        @empty
            <p class="text-gray-400">Belum ada event voting aktif.</p>
        @endforelse
    </div>
@endsection
