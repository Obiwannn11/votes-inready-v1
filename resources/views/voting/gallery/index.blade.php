@extends('voting.layouts.app')

@section('title', $event->title . ' — Gallery')

@section('content')
    <div class="mb-10">
        <h1 class="text-4xl font-display font-black uppercase tracking-tight text-ink mb-2">{{ $event->title }}</h1>
        @if ($event->description)
            <p class="font-body text-grey font-medium max-w-3xl">{{ $event->description }}</p>
        @endif

        <div class="mt-4 flex flex-wrap gap-3 items-center">
            @if ($event->isClosed())
                <x-button variant="primary" href="{{ route('voting.results', $event->slug) }}">
                    Lihat Hasil Voting
                </x-button>
            @endif

            @if ($event->isVotingOpen())
                <span
                    class="inline-block text-xs font-display font-bold uppercase tracking-widest px-3 py-1 border-2 border-ink bg-success text-surface shadow-[2px_2px_0px_0px_var(--color-ink)]">
                    Voting Dibuka
                </span>
                @auth
                    <span class="text-xs font-display font-bold uppercase tracking-widest text-grey ml-2">Vote terpakai:
                        {{ $userVoteCount }}/3</span>
                @endauth
            @elseif($event->isClosed())
                <span
                    class="inline-block text-xs font-display font-bold uppercase tracking-widest px-3 py-1 border-2 border-ink bg-muted text-ink shadow-[2px_2px_0px_0px_var(--color-ink)]">
                    Voting Ditutup — Hasil Final
                </span>
            @endif
        </div>
    </div>

    <div class="flex gap-4 mb-8 flex-wrap border-b-4 border-ink pb-4">
        <a href="{{ route('voting.gallery', $event->slug) }}"
            class="font-display font-bold uppercase tracking-widest px-4 py-2 border-2 border-ink transition hover:bg-ink hover:text-surface shadow-[3px_3px_0px_0px_var(--color-ink)] {{ !$concentration ? 'bg-ink text-surface' : 'bg-surface text-ink' }}">
            Semua
        </a>

        @foreach (['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'] as $key => $label)
            <a href="{{ route('voting.gallery', [$event->slug, 'c' => $key]) }}"
                class="font-display font-bold uppercase tracking-widest px-4 py-2 border-2 border-ink transition hover:bg-ink hover:text-surface shadow-[3px_3px_0px_0px_var(--color-ink)] {{ $concentration === $key ? 'bg-ink text-surface' : 'bg-surface text-ink' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($submissions as $sub)
            @php
                $thumbnailUrl = $sub->thumbnail_path
                    ? (\Illuminate\Support\Str::startsWith($sub->thumbnail_path, 'images/')
                        ? asset($sub->thumbnail_path)
                        : \Illuminate\Support\Facades\Storage::url($sub->thumbnail_path))
                    : asset('images/placeholder-ss.png');
            @endphp

            <x-card padding="p-0" class="group block cursor-pointer">
                <a href="{{ route('voting.detail', [$event->slug, $sub->id]) }}" class="block">
                    <div class="relative border-b-4 border-ink">
                        <img src="{{ $thumbnailUrl }}" alt="{{ $sub->title }}"
                            class="w-full h-56 object-cover object-top grayscale-[20%] group-hover:grayscale-0 transition-all duration-300"
                            loading="lazy">

                        <x-badge type="{{ $sub->concentration }}" class="absolute top-4 left-4">
                            {{ $sub->concentration }}
                        </x-badge>

                        @if (isset($userVotes[$sub->concentration]) && (int) $userVotes[$sub->concentration] === $sub->id)
                            <span
                                class="absolute top-4 right-4 text-xs font-display font-bold uppercase tracking-widest px-3 py-1 border-2 border-ink bg-success text-surface shadow-[2px_2px_0px_0px_var(--color-ink)]">
                                Voted ✓
                            </span>
                        @endif
                    </div>

                    <div class="p-5">
                        <h3
                            class="font-display font-bold text-xl uppercase tracking-tight group-hover:text-primary-blue transition-colors">
                            {{ $sub->title }}</h3>
                        <p class="text-sm font-body text-grey font-medium mt-1 uppercase tracking-wider">
                            {{ $sub->submitter?->name ?? 'Peserta' }}</p>

                        @if ($event->isClosed())
                            <div
                                class="mt-4 inline-block px-3 py-1 bg-ink text-primary-yellow font-display font-bold text-sm tracking-widest uppercase">
                                {{ $voteCounts[$sub->id] ?? 0 }} vote
                            </div>
                        @endif
                    </div>
                </a>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card border="thick" class="text-center py-16">
                    <p class="font-display font-bold text-xl text-ink uppercase tracking-widest">
                        @if ($concentration)
                            Belum ada karya di konsentrasi ini.
                        @else
                            Belum ada karya yang di-approve.
                        @endif
                    </p>
                </x-card>
            </div>
        @endforelse
    </div>
@endsection
