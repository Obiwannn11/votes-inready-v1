@extends('voting.layouts.app')

@section('title', $submission->title)

@section('content')
    <a href="{{ route('voting.gallery', $event->slug) }}"
        class="font-display font-bold uppercase tracking-widest text-sm text-ink hover:text-primary-blue hover:-translate-x-1 transition-all inline-block mb-6">
        ← Kembali ke Gallery
    </a>

    @php
        $thumbnailUrl = $submission->thumbnail_path
            ? (\Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
                ? asset($submission->thumbnail_path)
                : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path))
            : asset('images/placeholder-ss.png');
    @endphp

    <div class="max-w-5xl">
        <div class="mb-8 border-l-4 border-ink pl-6 py-2">
            <x-badge type="{{ $submission->concentration }}" class="mb-3">
                {{ $submission->concentration }}
            </x-badge>
            <h1 class="text-4xl sm:text-5xl font-display font-black uppercase tracking-tight text-ink mb-2">
                {{ $submission->title }}</h1>
            <p class="font-body text-lg text-grey font-medium tracking-wide uppercase">oleh
                {{ $submission->submitter?->name ?? 'Peserta' }}</p>
        </div>

        <div class="mb-10 w-full border-4 border-ink shadow-[8px_8px_0px_0px_var(--color-ink)] bg-surface relative">
            <div class="absolute -top-3 -right-3 w-6 h-6 rounded-full bg-primary-yellow border-2 border-ink z-10"></div>
            <img src="{{ $thumbnailUrl }}" alt="{{ $submission->title }}"
                class="w-full max-h-[500px] object-cover bg-muted p-2">
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                @if ($submission->screenshots->count())
                    <section>
                        <h2
                            class="font-display font-black text-2xl uppercase tracking-widest border-b-4 border-ink pb-2 mb-4">
                            Screenshot</h2>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach ($submission->screenshots as $ss)
                                @php
                                    $imageUrl = $ss->image_path
                                        ? (\Illuminate\Support\Str::startsWith($ss->image_path, 'images/')
                                            ? asset($ss->image_path)
                                            : \Illuminate\Support\Facades\Storage::url($ss->image_path))
                                        : asset('images/placeholder-ss.png');
                                @endphp

                                <a href="{{ $imageUrl }}" target="_blank" rel="noopener noreferrer"
                                    class="block border-2 border-ink shadow-[4px_4px_0px_0px_var(--color-ink)] transition-transform hover:-translate-y-1 bg-surface p-1">
                                    <img src="{{ $imageUrl }}"
                                        class="w-full h-48 object-cover grayscale-[30%] hover:grayscale-0 transition duration-300"
                                        loading="lazy" alt="Screenshot karya {{ $submission->title }}">
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section>
                    <h2 class="font-display font-black text-2xl uppercase tracking-widest border-b-4 border-ink pb-2 mb-4">
                        Deskripsi</h2>
                    <x-card padding="p-6">
                        <p class="whitespace-pre-line font-body text-ink leading-relaxed">{{ $submission->description }}
                        </p>
                    </x-card>
                </section>
            </div>

            <div class="lg:col-span-1 space-y-6">
                @if ($submission->demo_url)
                    <section>
                        <h2 class="font-display font-black text-xl uppercase tracking-widest mb-3">Live Demo</h2>
                        <a href="{{ $submission->demo_url }}" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between gap-2 border-2 border-ink bg-surface px-4 py-3 shadow-[4px_4px_0px_0px_var(--color-ink)] hover:bg-primary-yellow transition-colors font-display font-bold break-all">
                            <span class="truncate">{{ $submission->demo_url }}</span>
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </section>
                @endif

                @if ($event->isClosed() && $voteCount !== null)
                    <x-card accent="triangle" class="text-center bg-ink text-surface">
                        <p class="font-display font-black text-6xl text-primary-yellow leading-none">{{ $voteCount }}
                        </p>
                        <p class="font-display font-bold uppercase tracking-widest text-sm mt-2">Total Vote</p>
                    </x-card>
                @endif

                <div class="sticky top-6">
                    <x-card border="thick" accent="circle">
                        @if ($event->isVotingOpen())
                            @auth
                                @if (isset($userVotes[$submission->concentration]) && (int) $userVotes[$submission->concentration] === $submission->id)
                                    <div
                                        class="bg-success text-surface px-6 py-4 border-2 border-ink text-center font-display font-bold uppercase tracking-widest shadow-[4px_4px_0px_0px_var(--color-ink)]">
                                        Voted ✓
                                    </div>
                                @elseif(isset($userVotes[$submission->concentration]))
                                    <div
                                        class="bg-muted text-ink px-6 py-4 border-2 border-ink text-center font-body font-medium text-sm">
                                        Sudah vote konsentrasi {{ $submission->concentration }}
                                    </div>
                                @else
                                    <div x-data="{ confirming: false }">
                                        <x-button variant="primary" class="w-full" @click="confirming = true">
                                            Vote Karya Ini
                                        </x-button>

                                        <!-- Modal Konfirmasi -->
                                        <div x-show="confirming" x-cloak
                                            class="fixed inset-0 bg-ink/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                                            x-transition.opacity>
                                            <div class="bg-surface border-4 border-ink p-8 max-w-sm w-full shadow-[8px_8px_0px_0px_var(--color-primary-yellow)]"
                                                @click.away="confirming = false">
                                                <h3 class="font-display font-black text-2xl uppercase tracking-tighter mb-2">
                                                    Konfirmasi Vote</h3>
                                                <p class="font-body text-ink mb-2">
                                                    Vote untuk <strong class="font-display">{{ $submission->title }}</strong>?
                                                </p>
                                                <p
                                                    class="text-sm font-bold text-primary-red mb-6 border-l-2 border-primary-red pl-2">
                                                    Vote tidak bisa diubah setelah dikonfirmasi.
                                                </p>

                                                <div class="flex gap-3">
                                                    <x-button variant="outline" class="flex-1" @click="confirming = false">
                                                        Batal
                                                    </x-button>

                                                    <form method="POST"
                                                        action="{{ route('voting.vote', [$event->slug, $submission->id]) }}"
                                                        class="flex-1">
                                                        @csrf
                                                        <x-button type="submit" variant="primary" class="w-full">
                                                            Pilih
                                                        </x-button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <x-button variant="secondary" href="{{ route('voting.login') }}" class="w-full">
                                    Login untuk Vote
                                </x-button>
                            @endauth
                        @elseif($event->isClosed())
                            <p class="text-center font-body text-grey font-medium py-3 uppercase tracking-widest">Voting
                                ditutup.</p>
                        @else
                            <p class="text-center font-body text-grey font-medium py-3 uppercase tracking-widest">Voting
                                belum dibuka.</p>
                        @endif
                    </x-card>
                </div>
            </div>
        </div>
    </div>
@endsection
