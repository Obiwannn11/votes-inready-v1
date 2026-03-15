@extends('voting.layouts.app')

@section('title', 'Hasil Voting - ' . $event->title)

@section('content')
    <div class="mb-10 text-center">
        <h1 class="text-4xl sm:text-5xl font-display font-black uppercase tracking-tight text-ink mb-2">Hasil Voting</h1>
        <p class="font-body text-xl font-bold text-primary-blue uppercase tracking-widest">{{ $event->title }}</p>

        @if ($event->voting_closed_at)
            <p
                class="font-body text-sm font-medium text-grey uppercase tracking-widest mt-4 bg-muted inline-block px-4 py-2 border-2 border-ink">
                Voting ditutup: {{ $event->voting_closed_at->format('d M Y, H:i') }} WITA
            </p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-12 max-w-4xl mx-auto">
        <x-card accent="circle" class="text-center bg-ink text-surface">
            <p class="font-display font-black text-6xl text-primary-yellow leading-none">{{ $totalVoters }}</p>
            <p class="font-display font-bold uppercase tracking-widest text-sm mt-2 text-surface/80">Total Voter</p>
        </x-card>
        <x-card accent="square"
            class="text-center bg-primary-blue text-surface border-4 border-ink shadow-[6px_6px_0px_0px_var(--color-ink)] hover:-translate-y-1 transition-transform">
            <p class="font-display font-black text-6xl text-surface leading-none">{{ $totalVotes }}</p>
            <p class="font-display font-bold uppercase tracking-widest text-sm mt-2 text-surface/80">Total Vote</p>
        </x-card>
    </div>

    @php
        $concentrationLabels = ['website' => 'Website', 'design' => 'Desain', 'mobile' => 'Mobile'];
        $hasAnyResults = collect($concentrationLabels)->contains(function ($label, $key) use ($results) {
            return isset($results[$key]) && $results[$key]->count() > 0;
        });
    @endphp

    @foreach ($concentrationLabels as $key => $label)
        @if (isset($results[$key]) && $results[$key]->count() > 0)
            <section class="mb-12 border-4 border-ink bg-surface shadow-[8px_8px_0px_0px_var(--color-ink)] relative">
                <div
                    class="absolute -top-4 -left-4 font-display font-black text-2xl uppercase tracking-widest bg-primary-yellow border-4 border-ink px-6 py-2 shadow-[4px_4px_0px_0px_var(--color-ink)] z-10 text-ink">
                    {{ $label }}
                </div>

                <div class="p-6 sm:p-8 pt-12 space-y-4">
                    @foreach ($results[$key] as $index => $submission)
                        @php
                            $thumbnailUrl = $submission->thumbnail_path
                                ? (\Illuminate\Support\Str::startsWith($submission->thumbnail_path, 'images/')
                                    ? asset($submission->thumbnail_path)
                                    : \Illuminate\Support\Facades\Storage::url($submission->thumbnail_path))
                                : asset('images/placeholder-ss.png');
                        @endphp

                        <article
                            class="border-4 border-ink bg-surface p-4 flex gap-4 items-center transition-all hover:-translate-y-1 shadow-[4px_4px_0px_0px_var(--color-ink)] {{ $index === 0 ? 'bg-primary-yellow/10 border-primary-yellow' : '' }}">
                            <div
                                class="w-12 h-12 flex items-center justify-center font-display font-black text-2xl border-2 border-ink shrink-0 {{ $index === 0 ? 'bg-primary-yellow text-ink shadow-[2px_2px_0px_0px_var(--color-ink)]' : 'bg-muted text-grey' }}">
                                {{ $index + 1 }}
                            </div>

                            <img src="{{ $thumbnailUrl }}" alt="Thumbnail karya {{ $submission->title }}"
                                class="w-20 h-20 object-cover border-2 border-ink shrink-0" loading="lazy">

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('voting.detail', [$event->slug, $submission->id]) }}"
                                    class="font-display font-bold text-xl uppercase tracking-tight text-ink hover:text-primary-blue transition-colors wrap-break-word block">
                                    {{ $submission->title }}
                                </a>
                                <p class="font-body text-sm font-medium text-grey uppercase tracking-widest">
                                    {{ $submission->submitter?->name ?? 'Peserta' }}</p>
                                @if ($index === 0)
                                    <span
                                        class="inline-block mt-2 text-xs font-display font-bold uppercase tracking-widest px-2 py-1 bg-ink text-primary-yellow">
                                        ★ Juara #1
                                    </span>
                                @endif
                            </div>

                            <div class="text-right shrink-0">
                                <p
                                    class="font-display font-black text-4xl {{ $index === 0 ? 'text-primary-red' : 'text-ink' }} leading-none">
                                    {{ $submission->votes_count }}
                                </p>
                                <p class="font-display font-bold uppercase tracking-widest text-xs text-grey">vote</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @if (!$hasAnyResults)
        <x-card padding="p-12" border="thick" class="text-center shadow-[6px_6px_0px_0px_var(--color-ink)] mb-8">
            <p class="font-display font-black text-xl text-ink uppercase tracking-widest">
                Belum ada karya approved pada event ini, jadi hasil voting belum bisa ditampilkan.
            </p>
        </x-card>
    @endif

    <div class="text-center mt-12 mb-8">
        <x-button variant="outline" href="{{ route('voting.gallery', $event->slug) }}">
            ← Kembali ke Gallery
        </x-button>
    </div>
@endsection
