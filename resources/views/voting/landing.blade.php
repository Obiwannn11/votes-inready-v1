@extends('voting.layouts.app')

@section('title', 'Inready VOTES — Voting On Talent Excellence & Showcase')

@section('content')
    <div class="text-center py-12 relative">
        <div class="absolute top-0 right-[20%] w-24 h-24 bg-primary-red border-4 border-ink rounded-full -z-10 opacity-80">
        </div>
        <div class="absolute bottom-0 left-[20%] w-24 h-24 bg-primary-blue border-4 border-ink rotate-12 -z-10 opacity-80">
        </div>

        <h1 class="text-4xl sm:text-5xl font-display font-black uppercase tracking-tight text-ink mb-2">Inready VOTES</h1>
        <p class="font-body text-grey font-medium tracking-wide uppercase text-sm sm:text-base mb-12">Voting On Talent
            Excellence & Showcase</p>

        <div class="max-w-2xl mx-auto space-y-4">
            @forelse($events as $event)
                <x-card accent="{{ $event->isVotingOpen() ? 'circle-success' : 'circle-muted' }}" class="text-left block hover:bg-surface cursor-pointer" hover="true">
                    <a href="{{ route('voting.gallery', $event->slug) }}" class="block">
                        <div class="flex justify-between items-start gap-3 pr-8">
                            <h2 class="font-display font-bold text-xl uppercase">{{ $event->title }}</h2>
                            <span
                                class="text-xs font-display font-bold tracking-widest px-3 py-1 border-2 border-ink uppercase
                                {{ $event->isVotingOpen() ? 'bg-success text-surface' : 'bg-muted text-ink' }}">
                                {{ $event->isVotingOpen() ? 'VOTE SEKARANG' : 'SELESAI' }}
                            </span>
                        </div>

                        @if ($event->description)
                            <p class="text-sm font-body text-grey mt-3">
                                {{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                        @endif
                    </a>
                </x-card>
            @empty
                <p
                    class="text-ink font-body font-medium bg-surface border-2 border-ink p-8 shadow-[4px_4px_0px_0px_var(--color-ink)] inline-block">
                    Belum ada event voting aktif.</p>
            @endforelse
        </div>
    </div>
@endsection
