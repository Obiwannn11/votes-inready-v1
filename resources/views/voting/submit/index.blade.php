@extends('voting.layouts.app')

@section('title', 'Daftar Event Submit Karya - Inready VOTES')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="section-title mb-4">Submit Karya Event</h1>
        <p class="section-subtitle mb-8">Pilih event yang sedang membuka tahap penerimaan karya untuk mengunggah karya Anda.
        </p>

        @if ($events->isEmpty())
            <div class="card bg-surface p-12 text-center">
                <div class="icon-container diamond mx-auto mb-6 bg-primary-red text-surface">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-linejoin="miter">
                        <path d="M21 21H3V3h18z"></path>
                        <path d="M3 9h18"></path>
                        <path d="M9 21V9"></path>
                    </svg>
                </div>
                <h3 class="font-display font-black text-2xl mb-2 text-ink uppercase">Belum Ada Event Terbuka</h3>
                <p class="font-body text-ink/80 max-w-md mx-auto">Saat ini belum ada event yang membuka tahap submission.
                    Silakan periksa kembali nanti secara berkala.</p>
                <x-button href="{{ route('voting.landing') }}" variant="outline" class="mt-6">
                    Kembali ke Beranda
                </x-button>
            </div>
        @else
            <div class="grid-2">
                @foreach ($events as $event)
                    <div class="card bg-surface p-6 flex flex-col items-start relative h-full">
                        <div class="card-accent circle bg-success"></div>
                        <div class="font-display text-[10px] font-bold tracking-widest text-success uppercase mb-2">●
                            Submission Open</div>
                        <h2 class="card-title text-xl mb-3 pr-6 leading-tight">{{ $event->title }}</h2>
                        <p class="card-body text-sm mb-6 flex-1 line-clamp-3">Pendaftaran karya untuk event
                            {{ $event->title }} telah dibuka. Pastikan karya Anda sesuai dengan ketentuan yang berlaku.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full mt-auto">
                            <x-button href="{{ route('voting.submit.form', $event) }}" variant="primary"
                                class="w-full justify-center">
                                Submit Karya
                            </x-button>
                            <x-button href="{{ route('voting.submit.status', $event) }}" variant="outline"
                                class="w-full justify-center">
                                Status Saya
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
