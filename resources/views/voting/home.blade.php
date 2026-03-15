@extends('voting.layouts.app')

@section('title', 'Inready VOTES — Voting On Talent Excellence & Showcase')

@section('content')
    {{-- Section 1: Hero --}}
    <div class="text-center py-16 md:py-24 border-b-4 border-ink mb-12">
        <h1 class="font-display font-black text-4xl md:text-5xl uppercase tracking-tight mb-4">
            Inready <span class="bg-primary-yellow px-2 border-2 border-ink">VOTES</span>
        </h1>
        <p class="font-body text-ink font-semibold text-lg md:text-xl mb-4 uppercase tracking-widest">
            Voting On Talent Excellence & Showcase
        </p>
        <p class="font-body text-ink/80 max-w-xl mx-auto mb-8">
            Platform showcase dan voting karya terbaik
            anggota Inready Workgroup di tiga konsentrasi.
        </p>
        <x-button href="{{ route('voting.landing') }}" variant="primary" class="text-lg px-8 py-4">
            Lihat Event Voting →
        </x-button>
    </div>

    {{-- Section 2: Tentang VOTES --}}
    <section class="py-12 mb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="font-display font-black text-3xl uppercase mb-6">Apa Itu Inready VOTES?</h2>
                <div class="w-16 h-2 bg-primary-yellow border-2 border-ink mb-6"></div>
                <p class="font-body text-ink/80 mb-6">
                    VOTES (Voting On Talent Excellence & Showcase) adalah platform
                    internal Inready Workgroup untuk menampilkan dan memberikan
                    apresiasi terhadap karya terbaik anggota.
                </p>
                <p class="font-body text-ink/80 mb-6">
                    Setiap periode, anggota mengirimkan karya di salah satu
                    dari tiga konsentrasi. Karya yang lolos kurasi admin
                    ditampilkan di gallery, dan seluruh member berhak memberikan vote.
                </p>
                <div class="space-y-4 font-body font-medium">
                    <div class="flex items-start gap-4">
                        <span class="w-6 h-6 bg-success border-2 border-ink flex items-center justify-center text-surface text-xs font-bold shrink-0 mt-0.5">✓</span>
                        <p class="text-ink"><strong>Transparan</strong> — hasil voting terbuka untuk semua</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="w-6 h-6 bg-success border-2 border-ink flex items-center justify-center text-surface text-xs font-bold shrink-0 mt-0.5">✓</span>
                        <p class="text-ink"><strong>Terkurasi</strong> — hanya karya yang lolos review yang tampil</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="w-6 h-6 bg-success border-2 border-ink flex items-center justify-center text-surface text-xs font-bold shrink-0 mt-0.5">✓</span>
                        <p class="text-ink"><strong>Satu suara per konsentrasi</strong> — setiap vote bermakna</p>
                    </div>
                </div>
            </div>
            <div class="relative flex justify-center">
                <!-- Image Logo INR dari Public -->
                <div class="w-full max-w-sm aspect-square bg-primary-blue border-4 border-ink shadow-[8px_8px_0px_0px_var(--color-ink)] flex items-center justify-center p-8 relative overflow-hidden group">
                    <img src="{{ asset('images/logo-inr.png') }}" alt="Inready Logo" class="w-3/4 h-auto drop-shadow-2xl transition-transform duration-500 group-hover:scale-110">
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3: Cara Kerja --}}
    <section class="border-t-4 border-ink py-16 mb-12">
        <h2 class="font-display font-black text-3xl uppercase text-center mb-12">Bagaimana VOTES Bekerja?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <x-card>
                <div class="w-12 h-12 rounded-full border-4 border-ink bg-primary-yellow text-ink flex items-center justify-center font-display font-black text-xl mb-6 shadow-[2px_2px_0px_0px_var(--color-ink)]">
                    1
                </div>
                <h3 class="font-display font-bold text-xl uppercase mb-3 text-ink">Submit Karya</h3>
                <p class="font-body text-ink/80 text-sm">
                    Anggota mengirimkan karya terbaik di konsentrasi
                    masing-masing lengkap dengan screenshot dan deskripsi.
                </p>
            </x-card>

            <x-card class="translate-y-0 md:translate-y-4">
                <div class="w-12 h-12 rounded-full border-4 border-ink bg-primary-red text-surface flex items-center justify-center font-display font-black text-xl mb-6 shadow-[2px_2px_0px_0px_var(--color-ink)]">
                    2
                </div>
                <h3 class="font-display font-bold text-xl uppercase mb-3 text-ink">Review & Kurasi</h3>
                <p class="font-body text-ink/80 text-sm">
                    Admin mereview dan approve karya yang layak tampil di gallery.
                </p>
            </x-card>

            <x-card class="translate-y-0 md:translate-y-8">
                <div class="w-12 h-12 rounded-full border-4 border-ink bg-primary-blue text-surface flex items-center justify-center font-display font-black text-xl mb-6 shadow-[2px_2px_0px_0px_var(--color-ink)]">
                    3
                </div>
                <h3 class="font-display font-bold text-xl uppercase mb-3 text-ink">Vote & Hasil</h3>
                <p class="font-body text-ink/80 text-sm">
                    Member login dan vote karya favorit di tiap konsentrasi. Hasil tampil transparan.
                </p>
            </x-card>
        </div>
    </section>

    {{-- Section 4: Konsentrasi --}}
    <section class="border-t-4 border-ink py-16 mb-12">
        <h2 class="font-display font-black text-3xl uppercase text-center mb-12">Tiga Konsentrasi Karya</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
            <!-- SE / Web -->
            <div class="bg-surface border-4 border-ink p-8 shadow-[6px_6px_0px_0px_var(--color-ink)] text-center hover:-translate-y-2 transition-transform duration-200">
                <div class="w-16 h-16 mx-auto mb-6 bg-primary-blue border-4 border-ink rounded-full flex items-center justify-center shadow-[2px_2px_0px_0px_var(--color-ink)]">
                    <span class="text-2xl">🌐</span>
                </div>
                <h3 class="font-display font-bold text-xl uppercase border-b-2 border-ink pb-2 inline-block mb-4">Website<br>Development</h3>
                <p class="font-body text-ink/80 text-sm">
                    Web app, landing page, sistem informasi
                </p>
            </div>

            <!-- Multimedia -->
            <div class="bg-surface border-4 border-ink p-8 shadow-[6px_6px_0px_0px_var(--color-ink)] text-center hover:-translate-y-2 transition-transform duration-200">
                <div class="w-16 h-16 mx-auto mb-6 bg-primary-red border-4 border-ink flex items-center justify-center shadow-[2px_2px_0px_0px_var(--color-ink)] rotate-12">
                    <span class="text-2xl -rotate-12">🎨</span>
                </div>
                <h3 class="font-display font-bold text-xl uppercase border-b-2 border-ink pb-2 inline-block mb-4">Multimedia<br>Desain</h3>
                <p class="font-body text-ink/80 text-sm">
                    UI/UX, graphic design, branding
                </p>
            </div>

            <!-- Networking/Mobile -->
            <div class="bg-surface border-4 border-ink p-8 shadow-[6px_6px_0px_0px_var(--color-ink)] text-center hover:-translate-y-2 transition-transform duration-200">
                <div class="w-16 h-16 mx-auto mb-6 bg-primary-yellow border-4 border-ink flex items-center justify-center shadow-[2px_2px_0px_0px_var(--color-ink)]" style="border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;">
                    <span class="text-2xl">📱</span>
                </div>
                <h3 class="font-display font-bold text-xl uppercase border-b-2 border-ink pb-2 inline-block mb-4">Mobile &<br>Networking</h3>
                <p class="font-body text-ink/80 text-sm">
                    Aplikasi Android/iOS & Jaringan
                </p>
            </div>
        </div>
    </section>

    {{-- Section 5: Submit Karya (Kondisional) --}}
    @if(isset($submissionEvents) && $submissionEvents->isNotEmpty())
    <section class="border-t-4 border-ink py-16 mb-12 bg-primary-yellow border-x-4 px-6 md:px-12 -mx-4 md:-mx-12 shadow-[8px_8px_0px_0px_var(--color-ink)]">
        <h2 class="font-display font-black text-3xl uppercase mb-8">Submission Sedang Dibuka</h2>

        <div class="grid grid-cols-1 {{ $submissionEvents->count() > 1 ? 'md:grid-cols-2' : '' }} gap-6 max-w-4xl">
            @foreach($submissionEvents as $event)
            <div class="bg-surface border-4 border-ink p-6 shadow-[4px_4px_0px_0px_var(--color-ink)] relative">
                <x-badge type="success" class="mb-4 inline-block">SUBMISSION DIBUKA</x-badge>
                <h3 class="font-display font-bold text-xl mb-2 uppercase">{{ $event->title }}</h3>

                @if($event->description)
                    <p class="font-body text-sm text-ink/80 mb-4 line-clamp-2">
                        {{ $event->description }}
                    </p>
                @endif

                @if($event->submission_deadline)
                    <div class="bg-canvas border-2 border-ink border-dashed px-3 py-2 text-sm font-semibold mb-6 flex items-center gap-2">
                        <span>⏰</span> Deadline: {{ \Carbon\Carbon::parse($event->submission_deadline)->format('d M Y, H:i') }} WITA
                    </div>
                @endif

                <x-button href="{{ route('voting.submit.form', $event->slug) }}" variant="info" class="w-full justify-center">
                    Submit Karya →
                </x-button>
            </div>
            @endforeach
        </div>

        <p class="font-body text-sm font-bold text-ink mt-6 opacity-80 flex items-center gap-2">
            <span>ℹ️</span> Kamu harus login sebagai member untuk submit karya.
        </p>
    </section>
    @endif

    {{-- Section 6: CTA Final --}}
    <section class="py-16 text-center border-t-8 border-ink mt-12 bg-surface">
        <p class="font-display font-black text-2xl uppercase mb-6">Siap melihat karya terbaik?</p>
        <x-button href="{{ route('voting.landing') }}" variant="danger" class="text-lg px-8 py-4 mb-8 uppercase tracking-widest shadow-[6px_6px_0px_0px_var(--color-ink)] hover:shadow-none hover:translate-y-1 hover:translate-x-1">
            Lihat Event Voting →
        </x-button>

        <div class="font-body text-xs font-semibold text-ink/60 space-y-1 mt-6 pt-6 border-t-2 border-dashed border-ink/20 max-w-md mx-auto">
            <p class="uppercase tracking-widest">Inready VOTES — Inready Workgroup</p>
            <p>Study Club IT · UIN Alauddin Makassar</p>
            <p>&copy; {{ date('Y') }} Inready Workgroup. All Rights Reserved.</p>
        </div>
    </section>
@endsection
